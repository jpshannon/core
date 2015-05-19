<?php
namespace werx\Core;

/**
 * Base console controller
 *
 * This class is to ConsoleApp as Controller is to WerxWebApp.
 */
abstract class Console
{
	/**
	 * @var \werx\Core\Config $config
	 * @deprecated 2.0 same functionality is provided by \werx\Core\AppContext
	 */
	public $config;

	/**
	 * @var \werx\Core\AppContext $context
	 */
	public $context;

	/**
	 * @var \werx\Core\WerxApp $app
	 */
	public $app;

	public function __construct($context, $cli_only = true)
	{
		$this->app = $context->getApp();
		$this->context = $context;
		$this->config = new Config($context); // help transition;
		if ($cli_only && $this->context->cli === false) {
			die("This is only available from CLI.\n");
		}
	}
}