<?php

namespace werx\Core;

use Symfony\Component\HttpFoundation\Request;

class WebAppContext extends AppContext
{

	/**
	 * @var Symfony\Component\HttpFoundation\Request
	 */
	public $request;

	/**
	 * The current controller
	 * 
	 * @var \werx\Core\Controller
	 */
	protected $controller;

	public function __construct(WerxApp $app)
	{
		parent::__construct($app);
		$this->request = $app->request;
	}

	/**
	 * The controller currently executing
	 *
	 * This property will not be available until an instance of the controller has been created.
	 * 
	 * @param \werx\Core\Controller $controller
	 * @return WebAppContext
	 */
	public function setController(Controller $controller)
	{
		$this->controller = $controller;
		return $this;
	}

	/**
	 * Gets the currently executing controller
	 * 
	 * @return \werx\Core\Controller
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Get the directory views will be located in.
	 * 
	 * @return string
	 */
	public function getViewsDir()
	{
		return $this->resolvePath($this->app['views_dir']);
	}

	/**
	 * Returns the base path excluding the the script name
	 *
	 * @return string
	 */
	public function getBasePath()
	{
		return $this->request->getBasePath();
	}

	/**
	 * Gets the base url of the app including the script name
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->request->getBaseUrl();
	}

	/**
	 * Gets the root url.
	 *
	 * The script name will be included depending on the value of WerxApp::$settings['expose_script_name']
	 * 
	 * @return string
	 */
	public function getRootUrl()
	{
		$include_script_name = $this->app['expose_script_name']; 
		return $include_script_name ? $this->getBaseUrl() : $this->getBasePath();
	}

	/**
	 * Gets the base uri of the app (including host & scheme)
	 *
	 * The script name will be included depending on the value of WerxApp::$settings['expose_script_name']
	 *
	 * ex: https:\\myapp.com\web
	 * ex: http:\\myapp.com:3000\web\index.php
	 * 
	 * @return string
	 */
	public function getBaseUri()
	{
		return $this->app['base_url'] . $this->getRootUrl();
	}

	/**
	 * Creates a absolute url for the relative path
	 * 
	 * The script name will be included depending on the value of WerxApp::$settings['expose_script_name']
	 *
	 * @param string $path
	 * @return string
	 */
	public function getUrl($path, array $qs = [])
	{
		if (!empty($qs)) {
			$path .= (strpos($path, "?") >= 0 ? "&" : "?") . http_build_query($qs);
		}
		return WerxApp::combineVirtualPath($this->getRootUrl(), ltrim($path, '/'));
	}

	/**
	 * Creates a fully-qualified uri for the relative path
	 *
	 * The script name will be included depending on the value of WerxApp::$settings['expose_script_name']
	 * 
	 * @param string $path
	 * @return string
	 */
	public function getUri($path, $include_base = true)
	{
		if($include_base) {
			$path = $this->getUrl($this->getRootUrl(), $path);
		}
		return $this->getBaseUri() . $path;
	}

	/**
	 * Gets the location of an asset
	 *
	 * This method never returns the name of the script being executed
	 * 
	 * @param  string  $resource
	 * @param  boolean $as_uri Whether or not to get the fully-qualified uri
	 * @return string
	 */
	public function getAsset($resource, $as_uri = false)
	{
		$resource = WerxApp::combineVirtualPath($this->getBasePath(), $resource);
		return $as_uri ? $this->app['base_url'] . $resource : $resource;
	}
}