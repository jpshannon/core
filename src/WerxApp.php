<?php

namespace werx\Core;

/**
 * WerxApp.
 *
 * @property-read /werx/Config/Container $config
 */
abstract class WerxApp implements \ArrayAccess
{
	/**
	 * @var []
	 */
	protected static $app = [];

	protected $modules = [];

	/**
	 * @var string
	 */
	protected $name = null;

	/**
	 * @var ServiceCollection
	 */
	protected $services;

	protected $settings;

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
			if (!array_key_exists($key, $settings)) {
				$this->settings[$key] = $value;
			}
		}
		$this->setName($this->settings['name']);
		static::addInstance($this);
		$this->initServices($this->services);
	}

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
	}

	public function run()
	{
		$start = $this->modules[0];
		$start->handle($this);
	}

	public function getServices($service = null)
	{
		if (!empty($service)) {
			return $this->services->get($service);
		}
		return $this->services;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getContext()
	{
		if ($this->context) {
			return $this->context;
		}
		return $this->context = new AppContext($this);
	}

	protected function createConfig()
	{
		$this->services->setSingleton('config', function ($sc) {
			$settings = $this->settings;
			$provider = new \werx\Config\Providers\ArrayProvider(self::combinePath($settings['app_dir'], $settings['config_dir']));
			return new \werx\Config\Container($provider, $settings['environment']);
		});
	}

	protected function setEnvironment($environment = 'local')
	{
		$settings = $this->settings;
		$env = self::combinePath($settings['app_dir'], $settings['config_dir'], $settings['environment_file']);
		$this->settings['environment'] = (file_exists($env) ? trim(file_get_contents($env)) : $environment);
	}

	protected function initServices(ServiceCollection $services)
	{
		return $services;
	}

	/**
	 * @return string
	 */
	public function getSrcDir()
	{
		return $this->settings['app_dir'];
	}

	/**
	 * @param string $file
	 * @return string
	 */
	public function getAppResourcePath($file = null)
	{
		return static::combinePath($this->getSrcDir(), $file);
	}

	/**
	 * ArrayAccces: Check if offset exists
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->settings);
	}

	/**
	 * ArrayAccess: Get the value at the requested offset
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->settings[$offset];
	}

	/**
	 * ArrayAccess: Check the value of the requested offset
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->settings[$offset] = $value;
	}

	/**
	 * ArrayAccess: Unset the value of the requested offset
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		urnset($this->settings[$offset]);
	}

	/**
	 * Get a WerxApp instance by name
	 * @param string $name
	 * @return null|WerxApp
	 */
	public static function getInstance($name = 0)
	{
		return isset(static::$app[$name]) ? static::$app[$name] : null;
	}

	public static function addInstance(WerxApp $app)
	{
		if(empty(static::$app)) {
			static::$app[] = $app;
		}
		static::$app[$app->getName()] = $app;
	}

	public static function removeInstance($app_name)
	{
		unset(static::$app[$app_name]);
	}

	/**
	 * Defaults configuring a werx app
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
				"expose_script_name" => false
			];
	}

	/**
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
