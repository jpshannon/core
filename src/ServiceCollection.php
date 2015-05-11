<?php

namespace werx\Core;

/**
 * ServiceCollection.
 */
final class ServiceCollection implements \ArrayAccess
{
	private $services = [];

	public function __construct($services = [])
	{
		$this->register($services);
	}

	public function register($services)
	{
		foreach($services as $key => $value) {
			$this->set($key, $value);
		}
		return $this;
	}

	public function set($key, $value, $overwrite = false)
	{
		if(!$this->has($key) || $overwrite === true) {
			$this->services[$key] = $value;
		}
		return $this;
	}

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

	public function get($key, $default = null)
	{
		if(!$this->has($key)) {
			$this->evaluate($default);
		}
		return $this->evaluate($this->services[$key]);
	}

	public function has($key)
	{
		return array_key_exists($key, $this->services);
	}

	private function evaluate($value)
	{
		$invokable = is_object($value) && method_exists($value,'__invoke') || is_callable($value);
		return $invokable ? $value($this) : $value;
	}

	public function offsetExists ($offset)
	{
		return $this->has($offset);
	}

	public function offsetGet ( $offset )
	{
		return $this->has($offset) ? $this->get($offset) : null;
	}

	public function offsetSet ( $offset , $value )
	{
		$this->set($offset, $value);
	}

	public function offsetUnset ( $offset )
	{
		unset($this->services[$offset]);
	}
}
