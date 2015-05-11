<?php 
namespace \werx\Core;

class Console
{
	/**
	 * @var \werx\Core\WebAppContext $config
	 */
	public $config;

	/**
	 * @var \werx\Core\WebAppContext $context
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
		$this->config = $context; // help transition;
		if ($cli_only && $this->context->cli === false) {
			die("This is only available from CLI.\n");
		}
	}
} 