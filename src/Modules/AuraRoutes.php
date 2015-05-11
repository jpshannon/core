<?php
namespace werx\Core\Modules;

use werx\Core\Module;
use werx\Core\WerxApp;

class AuraRoutes extends Module
{

	public function config(WerxApp $app)
	{
		$services = $app->getServices();

		$services->setSingleton('router', function ($sc) {
			$settings = $this->settings;
			$factory = new \Aura\Router\RouterFactory;
			$router = $factory->newInstance();
			$router->add('default', '/{controller,action,id}')
					->setValues(['controller' => $settings['controller'], 'action' => $settings['action']]);
			return $router;
		});

		$routes_file = $app->getAppResourcePath('config/routes.php');

		if (file_exists($routes_file)) {
			// Let the app specify it's own routes.
			include_once($routes_file);
		}

	}

	public function handle(WerxApp $app)
	{
		// What resource was requested?
		$request = $app->request;

		$path = $request->getPathInfo();

		// Remove trailing slash from the path. This gives us a little more forgiveness in routing
		if ($path != '/') {
			$path = rtrim($path, '/');
		}

		// Find a matching route
		$route = $app->router->match($path, $_SERVER);

		if (!$route) {
			return $app->pageNotFound();
		}

		list($controller, $action, $params) = $this->getAction($route);

		$app['route_name'] = empty($route->name) ? 0 : $route->name;
		$app['controller'] = strtolower($controller);
		$app['action']  = strtolower($action);
		$app['route_params'] = $params;

		if (substr($controller, 0, 1) == '\\') {
			// Fully qualified namespace
			$class = $controller;
			$app['controller'] = strtolower(last(explode('\\', $controller)));
		} else {
			// instantiate the controller class from the default namespace
			$class = join('\\', [$app['namespace'], 'Controllers', $controller]);
		}

		if (!class_exists($class)) {
			return $app->pageNotFound();
		}
		$page = new $class($this->createContext());
		$this->callNamed($page, $action, $params);
	}

	/**
	 * @param /Aura/Router/Route $route
	 * @return array
	 */
	public function getAction(\Aura\Router\Route $route)
	{
		// does the route indicate a controller?
		if (isset($route->params['controller'])) {
			$namespace = "";

			if (isset($route->params['namespace'])) {
				$namespace = rtrim($route->params['namespace'], '\\') . '\\';
			}

			// explode out our route parts in case there are any namespaces
			$controller_parts = explode('\\', $route->params['controller']);

			// only convert on the last part since that is the controller
			// ucfirst after camel_case because laravel's camel_case doesn't
			// uppercase the first letter
			$controller_parts[count($controller_parts) - 1] = ucfirst(camel_case($controller_parts[count($controller_parts) - 1]));

			// put back together the route parts
			$controller = implode('\\', $controller_parts);

			// if we found a namespace above, then prepend it to the controller
			if (!empty($namespace)) {
				$controller = $namespace . $controller;
			}
		} else {
			// use a default controller
			$controller = $app['controller'];
		}

		// does the route indicate an action?
		if (isset($route->params['action'])) {
			// take the action method directly from the route
			$action = $route->params['action'];
		} else {
			// use a default action
			$action = 'index';
		}

		$method_params = array_filter(
			array_diff_key($route->params, array_flip(['controller','action','namespace'])),
			function($v) {
				return $v !== null;
			}
		);
		return [$controller, $action, $method_params];
	}

	protected function callNamed($page, $method, $params)
	{
		if (!method_exists($page, $method)) {
			return $this->pageNotFound();
		}

		// don't use reflection unless we really need it!
		$pc = count($params);
		if ($pc == 1) {
		    return $page->$method(array_values($params)[0]);
		} elseif ($pc == 0) {
			return $page->$method(null); // for backwards compatibility with Core::Dispatcher
		}

		$method = new \ReflectionMethod($page, $method);
		if (!$method->isPublic()) {
			return $app->pageNotFound();
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
}