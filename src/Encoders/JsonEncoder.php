<?php
namespace werx\Core\Encoders;

use werx\Core\Encoder;

/**
 * JsonEncoder short summary.
 */
class JsonEncoder extends Encoder
{
	protected $encoding_options;

	/**
	 * Default flags for json_encode; value of:
	 *
	 * <code>
	 * JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
	 * </code>
	 *
	 * @const int
	 */
    const DEFAULT_JSON_FLAGS = 79;

    public function __construct($encoding_options = DEFAULT_JSON_FLAGS)
	{
		$this->encoding_options = $encoding_options;
		parent::__construct('json', ['application/json', 'text/json', 'application/x-json']);
	}

	public function encode($data)
	{
		// clear json_last_error();
		json_encode(null);

		$json = json_encode($data, $this->encoding_options);

		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new \InvalidArgumentException(sprintf('Unable to encode data to JSON in %s: %s', __CLASS__, json_last_error_msg()));
		}

		return $json;
	}
}
