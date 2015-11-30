<?php
namespace werx\Core\Middleware;

use werx\Core\WerxWebApp;
use werx\Core\Context;
use werx\Core\MiddlewareInterface;
use Aura\Router\Router;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * AuraRoutes short summary.
 */
class AuraRoutes implements MiddlewareInterface
{
	protected $router;
	protected $app;

	public function __construct(WerxWebApp $app)
	{
		$factory = new \Aura\Router\RouterFactory($app->resolvePath(''));
		$this->router = $factory->newInstance();
		$app->set('router', $this->router);
		$this->app = $app;
	}

	public function __invoke(Request $request, Response $response, callable $next)
	{
		$this->initializeRoutes($this->app, $this->router->getRoutes());
        $path = $request->getUri()->getPath();
        $server = $request->getServerParams();
        $path = $this->getPathInfo($path, $server);

		if ($route = $this->match($path, $server)) {
			$args = $this->getAction($route, $this->app);
			
			list($controller, $action, $params) = $args;

			$context = new Context($this->app, strtolower(substr(strrchr($controller, '\\'), 1)), $action, $params);
			$context->setRequest($request);
			$context->setResponse($response);

			if (!class_exists($controller)) {
				return (new \werx\Core\Controller($context))->notFound();
			}
			
			$page = new $controller($context);
			if ($this->app->authorize($context)) {
				return $this->execute($page, $context);
			} else {
				return $page->notFound();
			}
		}
		return (new \werx\Core\Controller(new Context($this->app, 'common', 'notfound', [])))->notFound();
	}

	public function initializeRoutes(WerxWebApp $app, \Aura\Router\RouteCollection $router)
	{
		$routes_file = $app->getAppResourcePath('config/routes.php');
		$load_default = true;
		if (file_exists($routes_file)) {
            $load_default = include_once($routes_file);
		}
		if ($load_default) {
			$router->add('default', '{/controller,action,id}')
				->setValues(['controller' => $app->get('controller'), 'action' => $app->get('action')]);
		}
	}

	public function match($path, $server)
	{
		$router = $this->router;
		
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
	 * @param WerxWebApp $app
	 * @return array
	 */
	public function getAction(\Aura\Router\Route $route, WerxWebApp $app)
	{
		$app->set('route_name', empty($route->name) ? 0 : $route->name);

		// does the route indicate a controller?
		$controller =  $app->get('controller');
		if (isset($route->params['controller'])) {
			$controller = $route->params['controller'];
		}

		$routeNs = (isset($route->params['namespace']) ? $route->params['namespace'] : null);
		$controller = $this->buildFqn($controller, $app->get('namespace'), $routeNs);

		// does the route indicate an action?
		$action = $app->get('action');
		if (isset($route->params['action'])) {
			$action = $route->params['action'];
		}

		$method_params = array_filter(
			array_diff_key($route->params, array_flip(['controller','action','namespace'])),
			function($v) {
				return $v !== null;
			}
		);

		return [$controller, $action, $method_params];
	}

	protected function execute($page, $context)
	{
		$method = $context->action;
		$params = $context->args;

		if (!method_exists($page, $method)) {
			return $page->notFound();
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
			return $page->notFound();
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

    /**
     * Prepares the path info.
     *
     * @return string path info
     */
    protected function getPathInfo($requestUri, $server)
    {
        $baseUrl = $this->getBaseUrl($requestUri, $server);

        if (null === $requestUri) {
            return '/';
        }

        $pathInfo = '/';

        $pathInfo = substr($requestUri, strlen($baseUrl));
        if (null !== $baseUrl && (false === $pathInfo || '' === $pathInfo)) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        } elseif (null === $baseUrl) {
            return $requestUri;
        }

        return (string) $pathInfo;
    }

    /**
     * Prepares the base URL.
     *
     * @return string
     */
    protected function getBaseUrl($requestUri, $server)
    {
        $filename = basename($server['SCRIPT_FILENAME']);

        if (basename($server['SCRIPT_NAME']) === $filename) {
            $baseUrl = $server['SCRIPT_NAME'];
        } elseif (basename($server['PHP_SELF']) === $filename) {
            $baseUrl = $server['PHP_SELF'];
        } elseif (basename(@$server['ORIG_SCRIPT_NAME']) === $filename) {
            $baseUrl = @$server['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $server['PHP_SELF'] ?: '';
            $file = $server['SCRIPT_FILENAME'] ?: '';
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
        }

        // Does the baseUrl have anything in common with the request_uri?
        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $prefix;
        }

        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, rtrim(dirname($baseUrl), '/'.DIRECTORY_SEPARATOR).'/')) {
            // directory portion of $baseUrl matches
            return rtrim($prefix, '/'.DIRECTORY_SEPARATOR);
        }

        $truncatedRequestUri = $requestUri;
        if (false !== $pos = strpos($requestUri, '?')) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if (strlen($requestUri) >= strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && $pos !== 0) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return rtrim($baseUrl, '/'.DIRECTORY_SEPARATOR);
    }

    /*
     * Returns the prefix as encoded in the string when the string starts with
     * the given prefix, false otherwise.
     *
     * @param string $string The urlencoded string
     * @param string $prefix The prefix not encoded
     *
     * @return string|false The prefix as it is encoded in $string, or false
     */
    private function getUrlencodedPrefix($string, $prefix)
    {
        if (0 !== strpos(rawurldecode($string), $prefix)) {
            return false;
        }

        $len = strlen($prefix);

        if (preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $string, $match)) {
            return $match[0];
        }

        return false;
    }
}
