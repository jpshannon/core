<?php

namespace werx\Core;

/**
 * A Werx Application
 *
 * @property-read /werx/Config/Container $config
 */
abstract class WerxApp implements \ArrayAccess
{
	/**
	 * Active apps
	 *
	 * @var WerxApp[]
	 */
	protected static $app = [];

	/**
	 * Modules to be run with the app
	 *
	 * Modules are executed first in last out. Typically you will register your main module first.
	 *
	 * @var Module[]
	 */
	protected $modules = [];

	/**
	 * The name of the app
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 * Collection of services configured for the app.
	 *
	 * Services should be registred from the initServices method or from Modules.
	 *
	 * @var ServiceCollection
	 */
	protected $services;

	/**
	 * Default settings for the app.
	 *
	 * Settings are a combination of WerxApp::$defaultSettings, $settings passed in from the constructor
	 * and settings found in config.php
	 *
	 * @var []
	 */
	protected $settings;

	/**
	 * Context for the app being executed
	 *
	 * @var AppContext|WebAppContext
	 */
	protected $context;

	public function __get($name)
	{
		if($this->services->has($name)) {
			return $this->services->get($name);
		}
		throw new \OutOfBoundsException ("$name is not available.");
	}

	public function __construct(array $settings = [])
	{
		$this->services = new ServiceCollection;
		$this->settings = array_merge(static::defaultSettings(), $settings);
		$this->setEnvironment($this->settings['environment']);
		$this->createConfig();
		foreach ($this->config->load('config') as $key => $value) {
			// allow settings passed in to the object prevail
			if (!array_key_exists($key, $settings)) {
				$this->settings[$key] = $value;
			}
		}
		$this->setName($this->settings['name']);
		static::addInstance($this);
		$this->initServices($this->services);
	}

	/**
	 * Adds a module to be run with the app.
	 *
	 * Modules are loaded FIFO.
	 *
	 * @param Module $module
	 * @return WerxApp
	 */
	public function addModule(Module $module)
	{
		if (in_array($module, $this->modules)) {
			$mc = get_class($module);
			throw new \RuntimeException("Attempting to add multiple instances of Module $mc.");
		}
		if (count($this->modules) > 0) {
			$module->setNext($this->modules[0]);
		}
		$module->config($this);
		array_unshift($this->modules, $module);
		return $this;
	}

	/**
	 * Runs the application
	 */
	public function run()
	{
		$start = $this->modules[0];
		return $start->handle($this);
	}

	/**
	 * Get the service collection registered with the application
	 *
	 * @param  string $service Get the specific services instead of the collection
	 * @param  bool   $default The $default value to return if no service can be found
	 * @return ServiceCollection|mixed If no service is specified, the full collection of services.
	 */
	public function getServices($service = null, $default = false)
	{
		if (!empty($service)) {
			return $this->services->get($service, $default);
		}
		return $this->services;
	}

	/**
	 * Get the application config container
	 *
	 * @param string $config The index of the config item to retrieve
	 * @param mixed $default The default value to return if no config with the given index can be found.
	 * @return \werx\Config\Container|mixed If no config item is passed.
	 */
	public function getConfig($config = null, $default = false)
	{
		$config = $this->services->get('config');
		if (!empty($config)) {
			return $config->get($config, $default);
		}
		return $config;
	}

	/**
	 * Sets the name of the application.
	 *
	 * Defaults to the value of WerxApp::$settings["name"]
	 *
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Gets the name of the app
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Gets the context of the application
	 *
	 * @return AppContext|WebAppContext
	 */
	public function getContext()
	{
		if ($this->context) {
			return $this->context;
		}
		return $this->context = new AppContext($this);
	}

	/**
	 * Creates the config container for the application and registers it as a service
	 *
	 * @return \werx\Config\Container
	 */
	protected function createConfig()
	{
		$this->services->setSingleton('config', function ($sc) {
			$settings = $this->settings;
			$provider = new \werx\Config\Providers\ArrayProvider(self::combinePath($settings['app_dir'], $settings['config_dir']));
			return new \werx\Config\Container($provider, $settings['environment']);
		});
		return $this->services->get('config');
	}

	/**
	 * Sets the application environment
	 *
	 * @param string $environment
	 */
	protected function setEnvironment($environment = 'local')
	{
		$settings = $this->settings;
		$env = self::combinePath($settings['app_dir'], $settings['config_dir'], $settings['environment_file']);
		$this->settings['environment'] = (file_exists($env) ? trim(file_get_contents($env)) : $environment);
	}

	/**
	 * Initialize services for the application
	 *
	 * @param  ServiceCollection $services
	 * @return ServiceCollection
	 */
	protected function initServices(ServiceCollection $services)
	{
		return $services;
	}

	/**
	 * Get the source directory of the application
	 *
	 * @return string
	 */
	public function getSrcDir()
	{
		return $this->settings['app_dir'];
	}

	/**
	 * Get the path to resource
	 *
	 * @param string $file
	 * @return string
	 */
	public function getAppResourcePath($file = null)
	{
		return static::combinePath($this->getSrcDir(), $file);
	}

	/**
	 * ArrayAccces: Check if offset exists
	 *
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->settings);
	}

	/**
	 * ArrayAccess: Get the value at the requested offset
	 *
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->settings[$offset];
	}

	/**
	 * ArrayAccess: Check the value of the requested offset
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->settings[$offset] = $value;
	}

	/**
	 * ArrayAccess: Unset the value of the requested offset
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		urnset($this->settings[$offset]);
	}

	/**
	 * Get a WerxApp instance by name
	 *
	 * @param string $name
	 * @return null|WerxApp
	 */
	public static function getInstance($name = 0)
	{
		return isset(static::$app[$name]) ? static::$app[$name] : null;
	}

	/**
	 * Add a WerxApp instance
	 *
	 * @param WerxApp $app
	 */
	public static function addInstance(WerxApp $app)
	{
		if(empty(static::$app)) {
			static::$app[] = $app;
		}
		static::$app[$app->getName()] = $app;
	}

	/**
	 * Remove a WerxApp instance
	 *
	 * @param WerxApp $app
	 */
	public static function removeInstance($app_name)
	{
		unset(static::$app[$app_name]);
	}

	/**
	 * Defaults settings of a WerxApp
	 *
	 * @return array
	 */
	public static function defaultSettings()
	{
		$meta = new \ReflectionClass(get_called_class());
		$namespace = $meta->getNamespaceName();
		$app_dir = dirname($meta->getFileName());
		$name = $meta->getName();
		return [
				"name" => $name,
				"app_dir" => $app_dir,
				"namespace" => $namespace,
				"config_dir" => "config",
				"views_dir" => "views",
				"environment_file" => "environment",
				"debug" => true,
				"controller" => "home",
				"action" => "index",
				"environment" => "local",
				"expose_script_name" => false,
				"compatibility_mode" => "2.0"
			];
	}

	/**
	 * Combines 1 or more paths together
	 *
	 * @param string $base The base path
	 * @param string $p,... The path to add
	 * @return string The combined path
	 */
	public static function combinePath($base, $p)
	{
		$paths = func_get_args();
		$count = func_num_args();
		$path = $base;

		for($i = 1; $i < $count; $i++) {
			if(substr($path, -1) !== DIRECTORY_SEPARATOR) {
				$path .= DIRECTORY_SEPARATOR;
			}
			$next = $paths[$i];
			if(substr($next, 0, 1) === DIRECTORY_SEPARATOR) {
				$path = $next;
			} else {
				$path .= $next;
			}
		}
		$s = strpos(':',$path);
		return $s !== false ? strstr($path,$s) : $path;
	}

	/**
	 * Combines 1 or more paths together using the a connector suitable for the web
	 *
	 * @param string $base The base path
	 * @param string $p,... The path to add
	 * @return string The combined path
	 */
	public static function combineVirtualPath($base, $p)
	{
		$paths = func_get_args();
		$count = func_num_args();
		$path = $base;

		for($i = 1; $i < $count; $i++) {
			if(substr($path, -1) !== '/') {
				$path .= '/';
			}
			$next = $paths[$i];
			if(substr($next, 0, 1) === '/') {
				$path = $next;
			} else {
				$path .= $next;
			}
		}
		$s = strpos(':',$path);
		return $path;
	}
}
