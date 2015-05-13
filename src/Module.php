<?php

namespace werx\Core;

abstract class Module
{
	protected $next = null;

	public function config(WerxApp $app)
	{
	}

	/**
	 * Handle an portion of the app request
	 *
	 * Implementors can optionaly return the value from handleNext.
	 * Unless there is good reason not to.
	 * 
	 * @param  WerxApp $app [description]
	 * @return [type]       [description]
	 */
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
			return $this->next->handle($app);
		}
		return false;
	}
}


