<?php

namespace werx\Core;

abstract class Module
{
	protected $next = null;

	public function config(WerxApp $app)
	{
	}

	abstract public function handle(WerxApp $app);

	public function setNext(Module $module)
	{
		$this->next = $module;
		return $this;
	}

	public function getNext()
	{
		return $this->next;
	}

	protected function handleNext(WerxApp $app)
	{
		if ($this->next) {
			$this->next->handle($app);
		}
	}
}


