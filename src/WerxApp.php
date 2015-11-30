<?php

namespace werx\Core;

use \werx\Config\Container as Config;

/**
 * A Werx Application
 *
 * @property-read /werx/Config/Container $config
 */
abstract class WerxApp
{
	/**
	 * Active apps
	 *
	 * @var WerxApp[]
	 */
	protected static $app = [];

	/**
	 * The name of the app
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 * @var Middleware
	 */
	protected $middleware;

	protected $authorization;

	public function __construct(array $settings = [])
	{
		$settings = array_merge(static::defaultSettings(), $settings);
		$this->config = $this->createConfig($settings);
        $this->config->load('config');
		$this->config->items['default'] = array_merge($this->config->items['default'], $settings);
		$this->setAuthorization([$this, 'defaultAuthorization']);
        $this->setName($this->get('name'));
		static::addInstance($this);
		$this->middleware = $this->loadMiddleware(new Middleware());
	}

	/**
	 * Runs the application
	 */
	public function run()
	{
        $message = $this->getMessageFactory();
        $middlware = $this->middleware;
		return $middlware($message->request(), $message->response());
	}

    /**
     * Load the middlware to be executed
     * 
     * @param Middleware $middlware 
     * @return Middleware
     */
    protected function loadMiddleware(Middleware $middlware)
    {
        return $middlware;
    }

	/**
	 * Get the application config container
	 *
	 * @param string $key The index of the config item to retrieve
	 * @param mixed $default The default value to return if no config with the given index can be found.
	 * @return \werx\Config\Container|mixed If no config item is passed.
	 */
	public function getConfig($key = null, $default = false)
	{
		if (!empty($key)) {
			return $this->config->get($key, $default);
		}
		return $this->config;
	}

	public function set($key, $value)
	{
		$this->config->items['default'][$key] = $value;
	}

	public function get($key)
	{
		return $this->config->items['default'][$key];
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
     * Get the current application environment
     * 
     * @return string 
     */
    public function getEnvironment()
    {
        return $this->get('environment');
    }

    /**
     * Flag indicationg if debuging is enabled.
     * 
     * @return bool
     */
    public function isDebug()
    {
        return $this->get('debug') === true;
    }

    /**
     * Flag indicating if the application is in CLI mode.
     * 
     * @return bool
     */
    public function isCli()
    {
        return php_sapi_name() === 'cli';
    }

	public function authorize(Context $context)
	{
		return call_user_func($this->authorization, $context);
	}

	public function setAuthorization(callable $authorization)
	{
		$this->authorization = $authorization;
	}

	public function defaultAuthorization(Context $context)
	{
		return true;
	}

	/**
	 * Creates the config container for the application and registers it as a service
	 *
	 * @return \werx\Config\Container
	 */
	protected function createConfig($settings)
	{
		$provider = new \werx\Config\Providers\ArrayProvider(self::combinePath($settings['app_dir'], $settings['config_dir']));
		return new \werx\Config\Container($provider, $this->resolveEnvironment($settings));
	}

	/**
	 * Get the application environment from settings
	 *
	 * @param string $environment
	 */
	protected function resolveEnvironment(array $settings)
	{
		$env = self::combinePaths($settings['app_dir'], $settings['config_dir'], $settings['environment_file']);
		if (file_exists($env)) {
			$settings['environment'] = trim(file_get_contents($env));
		}
		return $settings['environment'];
	}

    /**
     * Resolves the virtual path to a resource
     *
     * @param  string $path
     * @return string
     */
	public function resolvePath($path)
	{
        if (strpos($path, "/")===0) {
            return $path;
        }
        if (starts_with($path, "~/")) {
            $base_path = $this->get('base_path');
            if (!empty(pathinfo($base_path, PATHINFO_EXTENSION))) {
                $base_path = dirname($base_path);
            }
            return static::combinePath($base_path, ltrim($path, "~/"), "/");
        }
        return static::combinePath($this->get('base_path'), $path, "/");
	}

	/**
	 * Get the source directory of the application
	 *
	 * @return string
	 */
	public function getSrcDir()
	{
		return $this->get('app_dir');
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

    public function setMessageFactory(callable $factory)
    {
        $this->set('message_factory', $factory);
    }

	/**
	 * Get a MessageFactory instance
	 * @throws \InvalidArgumentException 
	 * @return MessageFactory
	 */
	public function getMessageFactory()
	{
		$factory = $this->get('message_factory');
		if (!class_exists($factory)) {
			throw new \InvalidArgumentException("The class '$factory' does not exist.");
		}
		$obj = new $factory();
		if (!is_a($obj, 'werx\\Core\\MessageFactory')) {
			throw new \InvalidArgumentException("The class '$factory' does not implement 'werx\\Core\\MessageFactory'.");
		}
		return $obj;
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
				"message_factory" => "werx\\Core\\Utils\\ZendMessageFactory",
                "base_path" => false
			];
	}

	/**
	 * Combines 1 or more paths together
	 *
	 * @param string $base The base path
	 * @param string $p,... The path(s) to add
	 * @return string The combined path
	 */
	public static function combinePaths($base, $p)
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
		return $s !== false ? substr($path,$s) : $path;
	}

    public static function combinePath($base, $path, $separator = DIRECTORY_SEPARATOR)
    {
        if(substr($base, -1) !== $separator) {
			$base .= $separator;
		}
		if(substr($path, 0, 1) === $separator) {
			$base = $path;
		} else {
			$base .= $path;
		}
        return $base;
    }
}
