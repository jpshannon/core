<?php

namespace werx\Core;

class AppContext
{
	/**
	 * The application the context belongs to
	 * 
	 * @var WerxApp
	 */
	protected $app;

	/**
	 * The config for this app
	 * 
	 * @var \werx\Config\Container
	 */
	public $config;

	/**
	 * The base path of the application
	 * 
	 * @var string
	 */
	public $base_path;

	/**
	 * The current application environment (dev, test, prod, etc)
	 * 
	 * @var string
	 */
	protected $environment;

	/**
	 * Flag indicating if debug information should be included.
	 * 
	 * @var bool
	 */
	public $debug;

	/**
	 * Flag indicating if the app was initiated by the cli
	 * 
	 * @var bool
	 */
	public $cli;

	public function __construct(WerxApp $app)
	{
		$this->app = $app;
		$this->base_path = $app['app_dir'];
		$this->environment = $app['environment'];
		$this->debug = $app['debug'] !== false;
		$this->cli = php_sapi_name() == 'cli';
		$this->config = $app->config;
	}

	public function __call($method, $args = [])
	{
		return call_user_func_array([$this->config, $method], $args);
	}

	/**
	 * Resolves the phyiscal path to a resource
	 * 
	 * @param  string $path
	 * @return string
	 */
	public function resolvePath($path)
	{
		return WerxApp::combinePath($this->base_path, $path);
	}

	/**
	 * Gets current application environment (dev, test, prod, etc)
	 * 
	 * @return string
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * Gets the application the context belongs to
	 * 
	 * @return WerxApp|WerxWebApp
	 */
	public function getApp()
	{
		return $this->app;
	}
}

