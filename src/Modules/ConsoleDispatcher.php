<?php

namespace werx\Core\Modules;

use werx\Core\Module;
use werx\Core\WerxApp;

class ConsoleDispatcher extends Module
{
	public function handle(WerxApp $app)
	{
		$args = $app->getArgs();

		$controller = ucfirst($args[1]);
		$job_name = $args[2];
		$args = array_slice($args, 3);

		if (empty($controller)) {
			die("You must pass in a controller to use.\n");
		}

		if (empty($job_name)) {
			die("You must pass in a method to call.\n");
		}

		$class_name = implode("\\", [$app['namespace'], 'Controllers', $controller]);

		if (!class_exists($class_name, true)) {
			die("Controller {$class_name} does not exist.\n");
		}

		$class = new $class_name($app->getContext());

		if (!method_exists($class, $job_name)) {
			die("Method {$job_name} does not exist for controller {$class_name}.\n");
		}

		return call_user_func_array([$class, $job_name], $args);
	}
}

