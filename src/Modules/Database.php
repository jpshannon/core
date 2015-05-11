<?php

namespace \werx\Core\Modules;

use \werx\Core\Model;
use \werx\Core\WerxApp;
use \werx\Core\Database;

class Database extends Module
{
	public function handle(WerxApp $app)
	{
		$app->config->load('database', true);
		Database::init($app->config->database('default'));
		$this->handleNext($app);
	}
}

