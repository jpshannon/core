<?php

namespace werx\Core;

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
		$this->config = $app->getServices('config');
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

	/**
	 * Gets the underlying config store
	 *
	 * @return \werx\Config\Container
	 */
	public function getConfig()
	{
		return $this->config;
	}
}

