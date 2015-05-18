<?php

namespace werx\Core;

use werx\Core\Template;
use werx\Core\Config;
use werx\Core\Input;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Base web controller
 */
class Controller
{
	/**
	 * @var \werx\Core\Template $template
	 */
	public $template;

	/**
	 * @var \werx\Core\Config $config
	 * @deprecated 2.0 updated to \werx\Core\WebAppContext
	 */
	public $config;

	/**
	 * @var \werx\Core\WebAppContext $context
	 */
	public $context;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request $request
	 */
	public $request;

	/**
	 * @var \werx\Core\WerxApp $app
	 */
	public $app;

	/**
	 * @var \werx\Core\Input $input
	 */
	public $input;

	/**
	 * Data for use by the view
	 * @var array
	 */
	protected $view_data = [];

	public function __construct($context)
	{
		$context->setController($this);

		// Set the instance of our application
		$this->app = $context->getApp();
		$this->context = $context;
		$this->config = new Config($context);

		// Set up the template engine.
		$this->initializeTemplate();

		// Set up our HTTP Request object.
		$this->initializeRequest();
	}

	/**
	 * Set up the template system
	 *
	 * @param string $directory Filesystem path to the views directory.
	 */
	public function initializeTemplate($directory = null)
	{
		if (empty($directory)) {
			$directory = $this->context->getViewsDir();
		}

		// Remember what directory was set. We may have to reinitialize the template later and don't want to lose the previous setting.
		$this->views_directory = $directory;

		$this->template = new Template($directory);

		// Add url builder to the template.
		$extension = new \werx\Url\Extensions\Plates(null, null, $this->app['expose_script_name']);
		$this->template->loadExtension($extension);
	}

	/**
	 * Get info about the HTTP Request
	 *
	 * @var \Symfony\Component\HttpFoundation\Request $request
	 */
	public function initializeRequest($request = null)
	{
		$this->request = $this->app->getServices('request');

		// Shortcuts to the request object for cleaner syntax.
		$this->input = new Input($this->request);
	}

	/**
	 * Internal or External Redirect to the specified url
	 *
	 * @param string $url
	 * @param string|int|array $params
	 * @param bool $is_query_string
	 */
	public function redirect($url, $params = [], $is_query_string = false)
	{
		if (!preg_match('/^http/', $url)) {
			$url_builder = new \werx\Url\Builder(null, null, $this->app['expose_script_name']);

			if ($is_query_string && is_array($params)) {
				$url = $url_builder->query($url, $params);
			} else {
				$url = $url_builder->action($url, $params);
			}
		} else {
			// External url. Just do a basic expansion.
			$url_builder = new \Rize\UriTemplate;
			$url = $url_builder->expand($url, $params);
		}

		return $this->redirectTo($url);
	}

	public function redirectToRoute($route = null, array $data = [], $apply_current_params = true, array $qs = [])
	{
		return $this->redirectTo($this->routeUrl($route, $data, $apply_current_params, $qs));
	}

	public function redirectTo($url)
	{
		/**
		 * You MUST call session_write_close() before performing a redirect to ensure the session is written,
		 * otherwise it might not happen quickly enough to save your session changes.
		 */
		session_write_close();

		$response = new RedirectResponse($url);
		if ($this->app['compatibility_mode'] == "1.0") {
			$this->app->setResponse($response);
		}
		return $response;
	}

	/**
	 * Send a json response with given content.
	 *
	 * @param array $content
	 * @param int $status HTTP Status Code to send
	 * @param array $headers Additional headers to send
	 */
	public function json($content = [], $status=200, $headers = [])
	{
		$response = new JsonResponse(null, $status, $headers);
		$response->setData($content);
		if ($this->app['compatibility_mode'] == "1.0") {
			$this->app->setResponse($response);
		}
		return $response;
	}

	/**
	 * Send a jsonp response with given content.
	 *
	 * @param array $content
	 * @param string $jsonCallback
	 */
	public function jsonp($content = [], $jsonCallback = 'callback')
	{
		$response = new JsonResponse();
		$response->setData($content);
		$response->setCallback($jsonCallback);
		if ($this->app['compatibility_mode'] == "1.0") {
			$this->app->setResponse($response);
		}
		return $response;
	}

	/**
	 * Returns the view for the current action
	 * @param string|array $data The view to show if a string is passed, data to be passed to the view
	 * @param array $view_data View data to be applied to the current view
	 */
	public function view($view = null, array $data = [])
	{
		if(is_array($view)) {
			$data = $view;
			$view = sprintf('%s%s%s', $this->app['controller'], DIRECTORY_SEPARATOR, $this->app['action']);
		} else {
			if (strpos($view, DIRECTORY_SEPARATOR)===false) {
				$view = $view = sprintf('%s%s%s', $this->app['controller'], DIRECTORY_SEPARATOR, $view ?: $this->app['action']);
			}
		}
		$this->view_data = $data;
		return $this->template->output($view, $data);
	}

	/**
	 * Returns the current views custom data
	 */
	public function viewData()
	{
		return $this->view_data;
	}

	public function content($content, $content_type = "text/html")
	{
		$response = new Response($content, Response::HTTP_OK);
		$response->headers->set("Content-Type", $content_type);
		if ($this->app['compatibility_mode'] == "1.0") {
			$this->app->setResponse($response);
		}
		return $response;
	}

	public function asset($resource)
	{
		return $this->context->getAsset($resource);
	}

	public function url($template, $params = [], array $qs = [])
	{
		if (is_string($params) || is_int($params)) {
			$params = ['id' => $params];
			if (!preg_match('/\{id\}/', $template)) {
				$template = rtrim($template, '/') . '/{id}';
			}
		}
		if (!is_array($params)) {
			throw new \Exception('Invalid params');
		}
		$uri = \Rize\UriTemplate();
		return $this->context->getUrl($uri->expend($template, $params), $qs);
	}

	public function routeUrl($route = null, array $data = [], $apply_current_params = true, array $qs = [])
	{
		if (is_array($route)) {
			$data = $route;
			$route = $this->app['route_name'];
		}

		$url = $this->getRouteUrl($route, $data, $apply_current_params);
		return empty($qs) ? $url : $url . '?' . http_build_query($qs);
	}

	public function routeUri($route = null, array $data = [], $apply_current_params = true, array $qs = [])
	{
		return $this->context->getUri($this->routeUrl($route, $data, $apply_current_params, $qs));
	}

	protected function getRouteUrl($route_name, array $route_data = [], $apply_current_params = true)
	{
		$router = $this->app->router;
		if ($apply_current_params) {
			$route_data = array_merge(
				$this->app['route_params'],
				['controller' => $this->app['controller'], 'action' => $this->app['action']],
				$route_data
			);
		}
		$route = $router->generate($route_name ?: $this->app['route_name'], $route_data);
		return $this->context->getUrl($route);
	}

	public function __call($method = null, $args = null)
	{
		return false;
	}
}
