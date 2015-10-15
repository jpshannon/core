<?php
namespace werx\Core;

class Template extends \League\Plates\Template\Template
{

	protected $transforms = [];

	/**
	 * Create new Template instance.
	 * @param ViewEngine $engine
	 * @param string $name
	 */
	public function __construct($engine, $name, $transforms = [])
	{	
		$this->transforms = $transforms;
		parent::__construct($engine, $name);
	}


	public function __get($name)
	{
		if (!array_key_exists($name, $this->data)) {
			throw new \Exception("Property does not exist '$name'" . print_r($this->data,1));
		}
		
		$data = $this->data[$name];
		return ($this->$name = $this->transform($data, $name));
	}

	public function __set($name, $value)
	{
		$this->layoutData[$name] = $value;
	}

	public function setLayout($name, array $data = array())
	{
		$this->layout($name, $data);
	}

	/**
	 * Recursively sanitize output.
	 *
	 * @param $var
	 * @return array
	 */
	public function scrub($var, $functions)
	{
		if (is_string($var)) {
			// Sanitize strings
			return $this->batch($var, $functions);

		} elseif (is_array($var)) {
			// Sanitize arrays
			while (list($key) = each($var)) {
				// casting key to string for the case of numeric indexed arrays
				// i.e. 0, 1, etc. b/c 0 == any string in php
				$var[$key] = $this->transform($var[$key], $key);
			}

			return $var;
		} elseif (is_object($var)) {
			// Sanitize objects
			$values = get_object_vars($var);

			foreach ($values as $key => $value) {
				$var->$key = $this->transform($value, $key);
			}
			return $var;

		} else {
			// Not sure what this is. null or bool? Just return it.
			return $var;
		}
	}

	protected function transform($value, $key)
	{
		$transform = !array_key_exists((string)$key, $this->transforms) ? 'escape' : $this->transforms[$key];
		if ($transform === false) {
			return $value;
		}
		return $this->scrub($value, $transform);
	}

	/**
     * Apply multiple functions to variable.
     * @param  mixed  $var
     * @param  string $functions
     * @return mixed
     */
    protected function batch($var, $functions)
    {
        foreach (explode('|', $functions) as $function) {
            if ($this->engine->doesFunctionExist($function)) {
                $var = call_user_func(array($this, $function), $var);
            } elseif (is_callable(array($this,$function))) {
                $var = call_user_func(array($this,$function), $var);
            } elseif (is_callable($function)) {
                $var = call_user_func($function, $var);
            } else {
                throw new LogicException(
                    'The batch function could not find the "' . $function . '" function.'
                );
            }
        }

        return $var;
    }
}