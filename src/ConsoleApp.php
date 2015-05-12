<?php

namespace werx\Core;

/**
 * Console job runner
 */
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

	/**
	 * The args to be used to run the application
	 * 
	 * @return []
	 */
	public function getArgs()
	{
		return $this->args;
	}
}
