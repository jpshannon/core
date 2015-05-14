<?php

namespace werx\Core;

class Dispatcher extends WerxWebApp
{
	public function __construct($context)
	{
		parent::__construct($context);
		$this->addModule(new Modules\AuraRoutes);
	}
}
