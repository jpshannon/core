<?php

namespace werx\Core;

class Console extends WerxApp
{
	protected $args;

	public function __construct(array $settings = [], array $args = [])
	{
		parent::__construct($settings);
		$this->args = $args;
		$this->addModule(new Modules\ConsoleDispatcher);
	}

	public function getArgs()
	{
		return $this->args;
	}
}
