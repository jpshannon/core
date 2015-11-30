<?php
namespace werx\Core;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use werx\Core\ResponseFactory;

/**
 * Base web controller
 */
class Controller
{
	/**
	 * @var \werx\Core\ViewEngine $template
	 */
	public $template;

	/**
	 * @var \werx\Core\Context $context
	 */
	public $context;

	/**
	 * @var Request $request
	 */
	protected $request;

	/**
	 * @var \werx\Core\Input $input
	 */
	public $input;

	/**
	 * @var MessageFactory
	 */
	protected $response;


	/**
	 * Data for use by the view
	 * @var array
	 */
	protected $view_data = [];

	public function __construct(Context $context)
	{
		$this->context = $context;

		$this->template = $this->initializeTemplate();
		$this->request = $this->initializeRequest($context->getRequest());
		$this->response = $context->message;
	}

	/**
	 * Set up the template system
	 *
	 * @param string $directory Filesystem path to the views directory.
	 */
	protected function initializeTemplate()
	{
		$template = new ViewEngine($this->context->app->getViewsDir());
		$template->loadExtensions($this->context->app->getConfig('plates_extensions',[]));
		return $template;
	}

	/**
	 * Get info about the HTTP Request
	 *
	 * @param Request $request
	 * @return Request
	 */
	protected function initializeRequest(Request $request)
	{
		$this->input = new Input($request);
		return $this->request;
	}

	/**
	 * Internal or External Redirect to the specified url
	 *
	 * @param string $url
	 * @param string|int|array $params
	 * @param bool $is_query_string
	 * @return Response
	 */
	public function redirect($url, $params = [], $is_query_string = false)
	{
		if (!preg_match('/^http/', $url)) {
			if ($is_query_string && is_array($params)) {
				$url = $this->url($url, [], $params);
			} else {
				$url = $this->url($url, $params);
			}
		} else {
			// External url. Just do a basic expansion.
			$url_builder = new \Rize\UriTemplate;
			$url = $url_builder->expand($url, $params);
		}

		return $this->redirectTo($url);
	}

	/**
	 * Redirect to a route
	 * 
	 * @param mixed $route Route name to redirect to, or 
	 * @param array $data Route parameters
	 * @param mixed $apply_current_params Apply current route parameters
	 * @param array $qs Query string parameters to be included
	 * @return Response
	 */
	public function redirectToRoute($route = null, array $data = [], $apply_current_params = true, array $qs = [])
	{
		return $this->redirectTo($this->routeUrl($route, $data, $apply_current_params, $qs));
	}

	public function redirectTo($url)
	{
		return $this->response->redirect($url);
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
			$view = sprintf('%s%s%s', $this->context->controller, "/", $this->context->action);
		} else {
			if (strpos($view, "/")===false) {
				$view = $view = sprintf('%s%s%s', $this->context->controller, "/", $view ?: $this->context->action);
			}
		}
		return $this->content($this->template->render($view, $data));
	}

	public function content($content, $content_type = "text/html")
	{
		return $this->response->content($content, 200, ['Content-Type' => $content_type]);
	}

	public function notFound($message = "Page not found")
	{
		return $this->view('common/404',['message' => $message])->withStatus(404);
	}

	public function forbidden()
	{
		return $this->view('common/403')->withStatus(403);
	}

	public function unauthorized($www_authenticate = "Basic")
	{
		return $this->view('common/404')->withStatus(401)->withHeader('WWW-Authenticate', $www_authenticate);
	}

	public function asset($resource)
	{
		return $this->context->getAsset($resource);
	}

	public function url($template, $params = [], array $qs = [], $preserve = false)
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
		$uri = new \Rize\UriTemplate();
		return $this->context->getUrl($uri->expand($template, $params), $qs, $preserve);
	}

	/**
	 * Generates a route from the 
	 * @param string|array $route The name of the route to generate; or route parameters 
	 * @param array $data 
	 * @param mixed $apply_current_params 
	 * @param array $qs 
	 * @param mixed $preserve 
	 * @return \Psr\Http\Message\UriInterface
	 */
	public function routeUrl($route = null, array $data = [], $apply_current_params = true, array $qs = [], $preserve = false)
	{
		if (is_array($route)) {
			$data = $route;
			$route = $this->app->get('route_name');
		}

		return $this->getRouteUrl($route, $data, $apply_current_params, $qs, $preserve);
	}

	public function routeUri($route = null, array $data = [], $apply_current_params = true, array $qs = [])
	{
		return $this->context->getUri($this->routeUrl($route, $data, $apply_current_params, $qs));
	}

	protected function getRouteUrl($route_name, array $route_data = [], $apply_current_params = true, array $qs = [], $preserve = false)
	{
		$router = $this->context->app->get('router');
		if ($apply_current_params) {
			$route_data = array_merge(
				$this->context->args,
				['controller' => $this->context->controller, 'action' => $this->context->action],
				$route_data
			);
		}
		$route = $router->generate($route_name ?: $this->app->get('route_name'), $route_data);
		return $this->context->getUrl($route, $qs, $preserve);
	}

	public function __call($method = null, $args = null)
	{
		return false;
	}
}
