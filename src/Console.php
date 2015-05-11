<?php 
namespace \werx\Core;

class Console
{
	public function __construct($context, $cli_only = true)
	{
		$this->app = $context->getApp();
		$this->context = $context;
		if ($cli_only && $this->context->cli === false) {
			die("This is only available from CLI.\n");
		}
	}
} 