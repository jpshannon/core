<?php
namespace werx\Core;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\UriInterface as Uri;

/**
 * Context short summary.
 */
class Context
{
	/**
	 * @var WerxApp|WerxWebApp
	 */
	public $app;

	/**
	 * @var string
	 */
	public $controller;

	/**
	 * @var string
	 */
	public $action; 

	/**
	 * @var array
	 */
	public $args;

	/**
	 * @var MessageFactory
	 */
	public $message;

	/**
	 * @var Response
	 */
	protected $override_response;

	/**
	 * @var Request
	 */
	protected $request;

	public function __construct(WerxApp $app, $controller, $action, $args)
	{
		$this->app = $app;
		$this->controller = $controller;
		$this->action = $action;
		$this->args = $args;
		$this->message = $app->getMessageFactory();
	}

	/**
	 * @return Response
	 */
	public function getResponse()
	{
		return $this->override_response;
	}

	/**
	 * @param Response $response 
	 */
	public function setResponse(Response $response)
	{
		$this->override_response = $response;
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @param Request $request 
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * @return Uri
	 */
	public function getBaseUri()
	{
		return $this->request->getUri();
	}

	/**
	 * @param string $path 
	 * @param array $query 
	 * @param bool $preserve 
	 * @return Uri
	 */
	public function getUri($path, array $query = null, $preserve = false)
	{
		if ($preserve) {
			$query = array_merge($this->request->getQueryParams(), $query);
		}
		$uri = $this->getBaseUri()->withPath($path);
		if (!empty($query)) {
			$uri = $uri->withQuery(http_build_query($query));
		}
		return $uri;
	}

	/**
	 * @param string $path 
	 * @param array $query 
	 * @param bool $preserve 
	 * @return Uri
	 */
	public function getUrl($path, array $query = [], $preserve = false)
	{
		return $this->getRelativeUrl($this->getUri($path, $query, $preserve));
	}

	/**
	 * @param string $asset 
	 * @param bool $absolute 
	 * @return Uri
	 */
	public function getAsset($asset, $absolute = false)
	{
		$uri = $this->getBaseUri()->withPath($this->app->resolvePath('~/'.ltrim('/'.$asset)));
		if ($absolute) {
			$uri = $this->getRelativeUrl($uri);
		}
		return $uri;
	}

	protected function getRelativeUrl(Uri $uri)
	{
		return $uri->withScheme('')->withHost('');
	}

    public function getEncoder()
	{
		return Encoder::get($this->request->getAttribute('format_encoder'));
	}

}
