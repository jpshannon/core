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

	public function getRootUrl()
	{
		$include_script_name = $this->app['expose_script_name']; 
		return $include_script_name ? $this->getBaseUrl() : $this->getBasePath();
	}

	/**
	 * Gets the base uri of the app
	 *
	 * @return string
	 */
	public function getBaseUri()
	{
		return $this->app['base_url'] . $this->getRootUrl();
	}

	/**
	 * Creates a absolute url for the relative path
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

	public function getAsset($path, $as_uri = false)
	{
		$path = WerxApp::combineVirtualPath($this->getBasePath(), $path);
		return $as_uri ? $this->app['base_url'] . $path : $path;
	}
}