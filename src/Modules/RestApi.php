<?php

namespace werx\Core\Modules;

use \werx\Core\WerxApp;
use Aura\Router\Router;

/**
 * Rest.
 */
class RestApi extends \werx\Core\Module
{
	protected $is_api_request;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $request;

	public function config(WerxApp $app)
	{
		$config = $app->getContext()->config;
		$this->request = $app->getServices("request");

        $endpoint = $config->get('api:endpoint', '/api');
        $api_namespace = $config->get('api:namespace', false);

        if ($router = $app->getServices('router')) {
            $this->initDefaultApiRoutes($router, $endpoint, $api_namespace);
        }

		$this->is_api_request = str_contains($this->request->getPathInfo(), $endpoint);
	}

    public function initDefaultApiRoutes(Router $router, $endpoint, $namespace)
    {
        $router->attach('api', $endpoint . '/{controller}', function($router) use($namespace) {
            if (!empty($namespace)) {
                $router->addValues(['namespace' => $namespace]);
            }
            $router->addGet('all', '', 'all');
            $router->addGet('add', '/add', 'add');
            $router->addGet('get', '/{id}', 'get');
            $router->addPost('post','', 'post');
            $router->addPut('put','/{id}', 'put');
            $router->addDelete('delete', '/{id}', 'delete');
            $router->addOptions('options', '', function($params) {
                $app = WerxApp::getInstance();
                $response = new \Symfony\Component\HttpFoundation\Response(null, 204);
                $response->headers->add(["Allow" => $app->getServices('config')->get('api:options:allow', 'GET, HEAD, POST, PUT, DELETE, OPTIONS')]);
                $response->sendHeaders();
                return $response;
            });
        });
    }

	public function handle(WerxApp $app)
	{
		if (!$this->is_api_request) {
			return $this->handleNext($app);
		}

        try {
			$response = $this->handleNext($app);
		}
        catch (\Exception $e) {

			$message = $e->getMessage();
			if ($app['debug'] === false) {
				$message = "There was a problem with the application.";
			}

			$response = \werx\Core\Api::negotiateResponse(['errors' => true, 'message' => $message], 500);
		}

        $headers = $app->getServices('config')->get('api:headers', []);
		foreach ($headers as $key => $value)
		{
            $response->headers->add($key, $value);
		}
        return $response;
	}
}
