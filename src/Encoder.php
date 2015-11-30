<?php
namespace werx\Core;

/**
 * Encoder short summary.
 */
abstract class Encoder
{
	protected $type;
	protected $mime_types;

	static $available = [];

	protected function __construct($type, array $mime_types)
	{
		$this->type = $type;
		$this->mime_type = $mime_types;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getMimeTypes()
	{
		return $this->mime_types;
	}

	public function getMimeType()
	{
		return $this->mime_types[0];
	}

    abstract public function encode($data);

	public static function register(Encoder $encoder)
	{
		static::$available[$encoder->getType()] = $encoder;
	}

	public static function getAvailableTypes()
	{
		return array_keys(static::$available);
	}

	public static function getPriorities()
	{
		$encoders = array_values(static::$available);
		$arr = [];
		foreach($encoders as $e)
		{
			$arr = array_merge($arr, $e->getMimeTypes());
		}
		return $arr;
	}

	/**
	 * @param string $format 
	 * @return null|Encoder
	 */
	public static function get($format)
	{
		if (!array_key_exists($format, static::$available)) {
			return null;
		}
		return static::$available[$format];
	}
}
