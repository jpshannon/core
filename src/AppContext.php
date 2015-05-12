<?php

namespace werx\Core;

class AppContext
{
	/**
	 * @var WerxApp
	 */
	protected $app;

	/**
	 * @var \werx\Config\Container
	 */
	public $config;

	public $base_path;

	public $environment;

	public $debug;

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

	public function resolvePath($path)
	{
		return WerxApp::combinePath($this->base_path, $path);
	}

	public function getEnvironment()
	{
		return $this->environment;
	}

	public function getApp()
	{
		return $this->app;
	}
}

