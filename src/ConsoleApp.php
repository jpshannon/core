<?php

namespace werx\Core;

class ConsoleApp extends WerxApp
{
	protected $args;

	public function __construct(array $settings = [], array $args = [])
	{
		parent::__construct($settings);
		$this->getContext();
		$this->args = $args;
		$this->addModule(new Modules\ConsoleDispatcher);
	}

	public function getArgs()
	{
		return $this->args;
	}
}
