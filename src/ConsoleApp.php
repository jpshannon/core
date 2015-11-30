<?php

namespace werx\Core;

/**
 * Console job runner.
 */
class ConsoleApp extends WerxApp
{
	protected $args;

	public function __construct(array $settings = [])
	{
		parent::__construct($settings);
	}
    
    public function loadMiddleware(Middleware $middleware)
    {
        return $middleware->addInitial(new Middleware\ConsoleRunner($this));
    }
}
