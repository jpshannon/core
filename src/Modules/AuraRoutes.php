<?php
namespace werx\Core\Modules;

use werx\Core\Module;
use werx\Core\WerxApp;
use Symfony\Component\HttpFoundation\Request;
use Aura\Router\Router;

class AuraRoutes extends Module
{
	protected $args = [];

	public function config(WerxApp $app)
	{
		$services = $app->getServices();

		$services->setSingleton('router', function ($sc) use($app) {
			$factory = new \Aura\Router\RouterFactory;
			$router = $factory->newInstance();
			return $router;
		});
		$this->intializeRoutes($app);

		$route = $this->match($services->get('router'), $services->get('request')->getPathInfo(), $_SERVER);
		$this->args = $this->getAction($route, $app);
	}

	public function handle(WerxApp $app)
	{
		return $this->dispatch($app, $this->args);
	}

	public function intializeRoutes(WerxApp $app)
	{
		$routes_file = $app->getAppResourcePath('config/routes.php');

		$router = $app->getServices('router');
		if (file_exists($routes_file)) {
			// Let the app specify it's own routes.
			include_once($routes_file);
		}
		
		$routes = $router->getRoutes();
		if (empty($routes) || !array_key_exists('default', $routes)) {
			$router->add('default', '{/controller,action,id}')
				->setValues(['controller' => $app['controller'], 'action' => $app['action']]);
		}
	}

	public function match(Router $router, $path, $server)
	{
		// Remove trailing slash from the path. This gives us a little more forgiveness in routing
		if ($path != '/') {
			$path = rtrim($path, '/');
		}

		// Find a matching route
		$route = $router->match($path, $server);

		if (!$route) {
			return false;
		}

		return $route;
	}

	/**
	 * @param /Aura/Router/Route $route
	 * @param WerxApp $app
	 * @return array
	 */
	public function getAction(\Aura\Router\Route $route, WerxApp $app)
	{
		$app['route_name'] = empty($route->name) ? 0 : $route->name;

		// does the route indicate a controller?
		$controller =  $app['controller'];
		if (isset($route->params['controller'])) {
			$controller = $route->params['controller'];
		}
		
		$routeNs = (isset($route->params['namespace']) ? $route->params['namespace'] : null);
		$controller = $this->buildFqn($controller, $app['namespace'], $routeNs);
		
		$app['controller'] = strtolower(substr(strrchr($controller, '\\'), 1));

		// does the route indicate an action?
		$action = $app['action'];
		if (isset($route->params['action'])) {
			$action = $route->params['action'];
		}
		$app['action'] = strtolower($action);

		$method_params = array_filter(
			array_diff_key($route->params, array_flip(['controller','action','namespace'])),
			function($v) {
				return $v !== null;
			}
		);
		$app['route_params'] = $method_params;

		return [$controller, $action, $method_params];
	}

	public function dispatch(WerxApp $app, array $args)
	{
		list($controller, $action, $params) = $args;

		if (!class_exists($controller)) {
			return false;
		}

		$page = new $controller($app->getContext());
		return $this->callNamed($page, $action, $params);
	}

	protected function callNamed($page, $method, $params)
	{
		if (!method_exists($page, $method)) {
			return false;
		}

		// for better performance, don't use reflection unless we really need it
		$pc = count($params);
		if ($pc == 1) {
		    return $page->$method(array_values($params)[0]);
		} elseif ($pc == 0) {
			return $page->$method(null); // for backwards compatibility with Core::Dispatcher
		}

		$method = new \ReflectionMethod($page, $method);
		if (!$method->isPublic()) {
			return false;
		}

		// sequential arguments when invoking
		$args = [];
		// match params with arguments
		foreach ($method->getParameters() as $i => $param) {
			if (isset($params[$param->name])) {
				// a named param value is available
				$args[] = $params[$param->name];
			} elseif (isset($params[$i])) {
				// a positional param value is available
				$args[] = $params[$i];
			} elseif ($param->isDefaultValueAvailable()) {
				// use the default value
				$args[] = $param->getDefaultValue();
			}
		}
		
		// invoke with the args, and done
		return $method->invokeArgs($page, $args);
	}

	public function buildFqn($controller, $rootNS, $ns)
	{

		// we don't have a fqn controller, lets build it
		if (substr($controller, 0, 1) !== '\\') {
			
			$controller = studly_case(str_replace('\\', '\\ ', $controller));
			$namespace = $rootNS . '\\Controllers';

			if ($ns) {
				//we have a fqn namespace, use it
				if (substr($ns, 0, 1) === '\\') {
					$namespace = $ns;
				} else {
					$namespace = $namespace . '\\' . rtrim($ns, '\\');
				}
			}

			if (!empty($namespace)) {
				$controller = $namespace . '\\' . $controller;
			}
		}

		return $controller;
	}
}
