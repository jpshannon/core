<?php

namespace werx\Core;

class Dispatcher extends WerxWebApp
{
	public function __construct($settings = [])
	{
		parent::__construct($settings);
		$this->addModule(new Modules\AuraRoutes);
		$this->addModule(new Modules\NativeSession);
	}
}
