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
	 * @var \werx\Core\Context $app
	 */
	public $app;

	public function __construct(Context $context, $cli_only = true)
	{
		$this->context = $context;
		if ($cli_only && $context->app->isCli() !== true) {
			die("This is only available from CLI.\n");
		}
	}
}