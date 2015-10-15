<?php

namespace werx\Core;

use Symfony\Component\HttpFoundation\Request;

/**
 * The context information the app.
 *
 * All methods that can be accessed off of werx\Config\Container
 * can be used via the context.
 *
 * @method array	load()	load(string $group, $index = false)
 * @method      	set()	set(string $key, mixed $value, string $index_name = 'default')
 * @method mixed	get()	get(string $key, mixed $default_value = null, string $index_name = 'default')
 * @method array 	all()	all(string $index = null)
 * @method array 	clear() clear()
 */
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
		$this->request = $app->getServices('request');
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
	public function getBaseUri($include_root = true)
	{
		return $this->app['base_url'] . ($include_root ? $this->getRootUrl() : '');
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
			$path .= (strpos($path, "?") === false ? "?" : "&amp;") . http_build_query($qs);
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
	public function getUri($path, array $qs = [])
	{
		return $this->getBaseUri(false) . $this->getUrl($path, $qs);
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
		return $as_uri ? $this->getBaseUri(false) . $resource : $resource;
	}
}