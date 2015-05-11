<?php

namespace werx\Core\Modules;

use werx\Core\Module;
use werx\Core\WerxApp;

class ConsoleDispatcher extends Module
{
	public function handle(WerxApp $app)
	{
		$argv = $app->getArgs();

		$controller = ucfirst($argv[1]);
		$job_name = $argv[2];
		$args = array_slice($argv, 3);

		if (empty($controller)) {
			die("You must pass in a controller to use.\n");
		}

		if (empty($job_name)) {
			die("You must pass in a method to call.\n");
		}

		$class_name = implode("\\", [$namespace, 'Controllers', $controller]);

		if (!class_exists($class_name, true)) {
			die("Controller {$class_name} does not exist.\n");
		}

		$settings = ['namespace'=> $namespace];
		if (!is_null($app_dir)) {
			$settings['app_dir'] = $app_dir;
		}

		$class = new $class_name($app);

		if (!method_exists($class, $job_name)) {
			die("Method {$job_name} does not exist for controller {$class_name}.\n");
		}

		call_user_func_array([$class, $job_name], $args);
	}
}

