<?php

namespace werx\Core;

/**
 * Thin wrapper for werx\config.
 *
 * @method setProvider($provider = null)
 * @method setEnvironment($environment = null)
 * @method load($group = null, $index = false)
 * @method set($key, $value, $index_name = 'default')
 * @method get($key, $default_value = null, $index_name = 'default')
 * @method all($index = null);
 * @method clear();
 * @deprecated 2.0 Use \werx\Core\AppContext which provides similar functionality in the confinds of an app
 */
class Config
{
	protected $context;

	public function __construct($base_path = null)
	{
		$this->context = $base_path instanceof AppContext ? $base_path : WerxApp::getInstance()->getContext();
	}

	/**
	 * Work out the path to a resource in your app.
	 * @param string $resource
	 * @return string
	 */
	public function resolvePath($resource)
	{
		return $this->context->resolvePath($resource);
	}

	/**
	 * What environment are we running in? Local? Development? Production?
	 *
	 * @return string
	 */
	public function getEnvironment()
	{
		return $this->context->getEnvironment();
	}

	/**
	 * Get the the base url of our app
	 *
	 * @param bool $include_script_name Should we include the filename (index.php)?
	 * @return null|string The full URL to our app.
	 */
	public function getBaseUrl($include_script_name = false)
	{
		$path = $include_script_name ? $this->context->getBaseUrl() : $this->context->getBasePath();
		return $this->context->getApp()['base_url'] . $path;
	}

	/**
	 * Get the the base url of our app, including the filename.
	 *
	 * @return null|string The full URL to our app, including the file name (index.php)
	 */
	public function getScriptUrl()
	{
		return $this->getBaseUrl(true);
	}

	public function __call($method, $args = [])
	{
		return call_user_func_array([$this->context->config, $method], $args);
	}

	public function __get($property = null)
	{
		switch($property) {
			case 'base_url':
				return $this->getBaseUrl();
				break;
			case 'script_url':
				return $this->getScriptUrl();
				break;
			default:
				return parent::__get($property);
		}
	}
}
