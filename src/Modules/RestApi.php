<?php

namespace werx\Core\Modules;

use \werx\Core\WerxApp;
use Aura\Router\Router;

/**
 * Rest.
 */
class RestApi extends \werx\Core\Module
{
	protected $skip;

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

		if (!str_contains($this->request->getPathInfo(), $endpoint)) {
			$this->skip = true;
			return;
		}

		$headers = $config->get('api:headers', []);
		foreach ($headers as $key => $value)
		{
			header("$key: $value");
		}
	}

    public function initDefaultApiRoutes(Router $router, $endpoint)
    {
        $router->attach('api', $endpoint . '/{controller}', function($router) {
            $router->addGet('all', '', 'all');
            $router->addGet('add', '/add', 'add');
            $router->addGet('get', '/{id}', 'get');
            $router->addPost('post','', 'post');
            $router->addPut('put','/{id}', 'put');
            $router->addDelete('delete', '/{id}', 'delete');
            $router->addOptions('options', '', function($params) {
                $app = WerxApp::getInstance();
                $response = new \Symfony\Component\HttpFoundation\Response();
                $response->headers->add(["Allow" => $app->getServices('config')->get('api:options:allow', 'GET, POST, PUT, DELETE, OPTIONS')]);
                $response->sendHeaders();
                return $response;
            });
        });

    }

	public function handle(WerxApp $app)
	{
		if ($this->skip) {
			return $this->handleNext($app);
		}

        try {

			// @todo add more intelligent handling??
			return $this->handleNext($app);

		}
        catch (\Exception $e) {

			$message = $e->getMessage();
			if ($app['debug'] === false) {
				$message = "There was a problem with the application.";
			}

			$response = new \Symfony\Component\HttpFoundation\JsonResponse();
			$response->setData(['errors' => true, 'message' => $message]);
			$response->setStatusCode(500);
			return $response;
		}
	}
}
