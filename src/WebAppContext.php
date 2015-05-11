<?php

namespace werx\Core;

use Symfony\Component\HttpFoundation\Request;

class WebAppContext extends AppContext
{

	/**
	 * @var Request
	 */
	public $request;

	public function __construct(WerxApp $app)
	{
		parent::__construct($app);
		$this->request = $app->request;
	}

	public function getViewsDir()
	{
		return $this->resolvePath($this->app['views_dir']);
	}

	/**
	 * Returns the base path including the script name
	 * @return string
	 */
	public function getBasePath()
	{
		return $this->request->getBasePath();
	}

	/**
	 * Gets the base url of the app
	 * @return string
	 */
	public function getBaseUrl()
	{
		$include_script_name = $this->app['expose_script_name'];
		return $include_script_name ? $this->request->getBaseUrl() : $this->request->getBasePath();
	}

	/**
	 * Gets the base uri of the app
	 * @return string
	 */
	public function getBaseUri()
	{
		return $this->app['base_url'] . $this->getBaseUrl();
	}

	/**
	 * Creates a absolute url for the relative path
	 * @param string $path
	 * @return string
	 */
	public function getUrl($path)
	{
		return WerxApp::combineVirtualPath($this->getBaseUrl(), ltrim($path, '/'));
	}

	/**
	 * Creates a fully-qualified uri for the relative path
	 * @param string $path
	 * @return string
	 */
	public function getUri($path, $include_base = true)
	{
		if($include_base) {
			$path = $this->getUrl($this->getBaseUrl(), $path);
		}
		return $this->getBaseUrl() . $path;
	}
}