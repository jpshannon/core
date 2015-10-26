<?php

namespace werx\Core;

/**
 * A collection of services available to application
  */
final class ServiceCollection implements \ArrayAccess
{
	private $services = [];

	public function __construct($services = [])
	{
		$this->register($services);
	}

	/**
	 * Register a collection of services all at once
	 *
	 * @param  array $services
	 * @return ServiceCollection
	 */
	public function register($services)
	{
		foreach($services as $key => $value) {
			$this->set($key, $value);
		}
		return $this;
	}

	/**
	 * Set a new service
	 *
	 * @param string  $key       The key of the service can be found with
	 * @param mixed  $value      The value of the service
	 * @param boolean $overwrite Overwrite the already configured service with the same name
	 * @return ServiceCollection
	 */
	public function set($key, $value, $overwrite = false)
	{
		if(!$this->has($key) || $overwrite === true) {
			$this->services[$key] = $value;
		}
		return $this;
	}

	/**
	 * Set a new service that should only be initialized once
	 *
	 * @param string $key      The key of the service can be found with
	 * @param \Closure $factory The method used to instantiate the object
	 * @return ServiceCollection
	 */
	public function setSingleton($key, $factory)
	{
		$this->set($key, function($sc) use ($factory) {
			static $instance;
			if($instance === null) {
				$instance = $factory($sc);
			}
			return $instance;
		});
		return $this;
	}

	/**
	 * Gets a registered service
	 * @param  string $key     The key of the service
	 * @param  mixed $default  Value to be used if the service cannot be found
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		if(!$this->has($key)) {
			return $this->evaluate($default);
		}
		return $this->evaluate($this->services[$key]);
	}

	/**
	 * Determine if the service is registered
	 *
	 * @param  string  $key The key of the service
	 * @return boolean
	 */
	public function has($key)
	{
		return array_key_exists($key, $this->services);
	}

	private function evaluate($value)
	{
		$invokable = is_object($value) && method_exists($value,'__invoke') || is_callable($value);
		return $invokable ? $value($this) : $value;
	}

	/**
	 * ArrayAccess: Check if the offset exists
	 *
	 * @param  mixed $offset
	 * @return boolean
	 */
	public function offsetExists ($offset)
	{
		return $this->has($offset);
	}

	/**
	 * ArrayAccess: Get the specified offset
	 *
	 * @param  mixed $offset
	 * @return mixed
	 */
	public function offsetGet ( $offset )
	{
		return $this->get($offset, null);
	}

	/**
	 * ArrayAccess: Set the value of the offset
	 *
	 * @param  string $offset
	 * @param  mixed  $value
	 */
	public function offsetSet ( $offset , $value )
	{
		$this->set($offset, $value);
	}

	/**
	 * ArrayAccess: Unset the offset
	 *
	 * @param  mixed $offset
	 */
	public function offsetUnset ( $offset )
	{
		unset($this->services[$offset]);
	}
}
