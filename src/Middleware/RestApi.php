<?php
namespace werx\Core\Middleware;

use werx\Core\WerxWebApp;
use werx\Core\MiddlewareInterface;
use werx\Core\Encoder;
use Aura\Router\RouteCollection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Negotiation\Negotiator;
/**
 * Rest.
 */
class RestApi extends MiddlewareInterface
{
	protected $config;
	protected $is_debug;
	protected $default_format;

	public function __construct(array $config, $debug = false)
	{
		$this->config = $config;
		$this->is_debug = $debug;
		$this->default_format = array_key_exists('default_format', $config) ? $config['default_format'] : false;
	}

	public function __invoke(Request $request, Response $response, callable $next)
	{
		if (!str_contains($request->getUri()->getPath(), $this->get('endpoint', '/api'))) {
			return $next($request, $response);
		}

		$format = $this->getFromExtension($request) ?: $this->getFromHeader($request) ?: $this->default_format;

		if (!$format) {
			return $this->respond(415);
		}
		$request = $request->withAttribute('format_encoder', $format);

		try {
			$result = $next($request, $response);

			if (is_array($result)) {
				$response = $response->withStatus($result[0])->withHeader('Allow', $result[1]);
			}
			else { 
				$response = $result;
			}
		}
		catch (\Exception $e) {

			$message = $e->getMessage();
			if ($this->is_debug !== true) {
				$message = "There was a problem with the application.";
			}
			$response = $this->respond(500, Encoder::get($format), $message);
		}

		if (!$response) {
			$response = $this->respond(404, Encoder::get($format), ['errors' => true, 'message' => 'Requested resource not found']);
		}
		$headers = $this->get('headers', []);
		foreach ($headers as $key => $value) {
			$response = $response->withHeader($key, $value);
		}
		return $response;
	}

	protected function get($key, $default= null)
	{
		if (array_key_exists($key, $this->config)) {
			return $this->config[$key];
		}
		return $default;
	}

	/**
	 * Returns the format using the file extension.
	 *
	 * @return null|string
	 */
    protected function getFromExtension(Request $request)
    {
        $format = strtolower(pathinfo($request->getUri()->getPath(), PATHINFO_EXTENSION));

        return isset($this->formats[$format]) ? $format : null;
    }

	/**
	 * Returns the format using the Accept header.
	 *
	 * @return null|string
	 */
    protected function getFromHeader(Request $request)
    {
        $accept = $request->getHeaderLine('Accept');

        if (empty($accept)) {
            return;
        }

        $priorities = call_user_func_array('array_merge', array_values($this->formats));

        try {
            $accept = (new Negotiator())->getBest($accept, $priorities);
        }
		catch (\Exception $exception) {
            return;
        }

        if ($accept) {
            $accept = $accept->getValue();

            foreach ($this->formats as $extension => $headers) {
                if (in_array($accept, $headers)) {
                    return $extension;
                }
            }
        }
    }

	protected function respond($status, Encoder $encoder = null, $content = null)
	{
		$response = WerxWebApp::getInstance()->getResponseFactory();
		if (is_null($content)) {
			return $response->noContent($status);
		}
		return $response->encoded($encoder, $content, $status);
	}

	public static function registerApiRoutes(RouteCollection $router, $endpoint = '/api', $version = '', $route_name = 'api')
	{
		if (!empty($version)) {
			$endpoint .= '/'.$version;
		}
		$router->attach($route_name, $endpoint . '/{controller}', function($router) use($version) {
			if (!empty($version)) {
				$router->addValues(['namespace' => $version]);
			}
			$router->addGet('get', '/{id}', 'get');
			$router->addGet('add', '/add', 'add');
			$router->addGet('all', '', 'all');
			$router->addPost('post','', 'post');
			$router->addPut('put','/{id}', 'put');
			$router->addDelete('delete', '/{id}', 'delete');
			$router->addOptions('options', '', function($params) {
				$app = WerxWebApp::getInstance();
				return $app->getResponseFactory()->noContent(204, ['Allow' => $app->getConfig('api:options:allow', 'GET, HEAD, POST, PUT, DELETE, OPTIONS')]);
			});
		});
	}

}
