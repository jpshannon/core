<?php
namespace werx\Core\Encoders;

use werx\Core\Encoder;

/**
 * JsonpEncoder short summary.
 */
class JsonpEncoder extends JsonEncoder
{
	protected $callback;

    public function __construct($callback, $encoding_options = static::DEFAULT_JSON_FLAGS)
	{
		$this->validateCallback($callback);
		$this->callback = $callback;
		$this->encoding_options = $encoding_options;
		$this->type = "jsonp";
		$this->mime_types = ['text/javascript', 'application/javascript', 'application/x-javascript'];
	}

	public function encode($data)
	{
		$json = parent::encode($data);
		return sprintf('/**/%s(%s);', $this->callback, $json);
	}

	private function validateCallback($callback)
	{
		$pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';
		$parts = explode('.', $callback);
		foreach ($parts as $part) {
			if (!preg_match($pattern, $part)) {
				throw new \InvalidArgumentException("The callback name '{$callback}' is not valid.");
			}
		}
	}
}
