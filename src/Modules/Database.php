<?php

namespace werx\Core\Modules;

use \werx\Core\Module;
use \werx\Core\WerxApp;
use \werx\Core\Database as DB;

class Database extends Module
{
	protected $connection_name;
	protected $initialized = false;

	public function __construct($connection_name = 'default')
	{
		$this->connection_name = $connection_name;
	}

	public function config(WerxApp $app)
	{
		if ($app->config->get("database_load_on_config", false)) {
			$this->setup($app);
		}
	}

	public function handle(WerxApp $app)
	{
		$this->setup($app);
		$this->handleNext($app);
		$this->tearDown($app);
	}

	protected function setup(WerxApp $app)
	{
		if ($this->initialized === false) {
			$app->config->load('database', true);
			DB::init($app->config->get($this->connection_name, null, 'database'));
		}
		$this->initialized = true;
	}

	protected function tearDown(WerxApp $app)
	{
		// TODO: maybe analyze queries etc.
	}
}
