<?php

namespace werx\Core;

abstract class Module
{
	protected $next = null;

	/**
	 * Configure the module with the given app.
	 *
	 * This is a good place to register any services the module wishes to expose,
	 * including potentially the module itself.
	 *
	 * @param WerxApp $app
	 */
	public function config(WerxApp $app)
	{
	}

	/**
	 * Handle a portion of the app request
	 *
	 * Implementors can optionaly return the value from handleNext.
	 * Unless there is good reason not to.
	 *
	 * @param  WerxApp $app
	 * @return mixed        Typically a response object for web apps, or error code for console apps.
	 */
	abstract public function handle(WerxApp $app);

	/**
	 * Sets the module to be executed next by this module
	 *
	 * @param Module $module
	 * @return Module
	 */
	public function setNext(Module $module)
	{
		$this->next = $module;
		return $this;
	}

	/**
	 * Get the next module to be executed
	 *
	 * @return Module
	 */
	public function getNext()
	{
		return $this->next;
	}

	/**
	 * Calls the next module in lines `handle` method and return the value
	 *
	 * @param WerxApp $app
	 * @return false|mixed false if there are no more modules to be exectued.
	 */
	protected function handleNext(WerxApp $app)
	{
		// yes, the assignment is intended.
		if ($next = $this->getNext()) {
			return $next->handle($app);
		}
		return false;
	}
}


