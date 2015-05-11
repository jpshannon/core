<?php

namespace werx\Core\Modules;

use \werx\Core\Model;
use \werx\Core\WerxApp;
use \werx\Core\Database;

class Database extends Module
{
	public function __construct($config = 'database', $item = 'default')

	public function handle(WerxApp $app)
	{
		$app->config->load($config, true);
		Database::init($app->config->get($item, null, $config));
		$this->handleNext($app);
	}
}

