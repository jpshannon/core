<?php

namespace werx\Core\Modules;

use \werx\Core\WerxApp;
use Aura\Router\RouteCollection;

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
		$this->request = $app->getServices("request");
		$this->is_api_request = str_contains($this->request->getPathInfo(), $app->getConfig('api:endpoint'));
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
