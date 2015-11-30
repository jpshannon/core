<?php

namespace werx\Core;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Wrapper to make working with ServerRequestInterface GET and POST data easier
 *
 * Exposes post/get data with friendlier (IMHO) syntax.
 */
class Input
{
	/**
	 * @var Request
	 */
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Fetch items from the $_POST array.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @param bool $deep
	 * @return array|mixed
	 */
	public function post($key = null, $default = null, $deep = false)
	{
		$post = $this->request->getParsedBody();
		return $this->getKeyDeep($post, $key, $default, $deep);
	}

	/**
	 * Fetch items from the $_GET array.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @param bool $deep
	 * @return array|mixed
	 */
	public function get($key = null, $default = null, $deep = false)
	{
		$get = $this->request->getQueryParams();
		return $this->getKeyDeep($get, $key, $default, $deep);
	}

	/**
	 * Summary of getKeyDeep
	 * 
	 * From Symfony\Component\HttpFoundation\ParamaterBag::get
	 * 
	 * @param array $array 
	 * @param string $path 
	 * @param mixed $default 
	 * @param  $deep 
	 * @throws \InvalidArgumentException 
	 * @return mixed
	 */
	protected function getKeyDeep(array $array, $path, $default, $deep)
	{
		if (empty($path)) {
			return $array;
		}

        if (!$deep || false === $pos = strpos($path, '[')) {
            return array_key_exists($path, $array) ? $array[$path] : $default;
        }

        $root = substr($path, 0, $pos);
        if (!array_key_exists($root, $array)) {
            return $default;
        }

        $value = $array[$root];
        $currentKey = null;
        for ($i = $pos, $c = strlen($path); $i < $c; ++$i) {
            $char = $path[$i];

            if ('[' === $char) {
                if (null !== $currentKey) {
                    throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "[" at position %d.', $i));
                }

                $currentKey = '';
            } elseif (']' === $char) {
                if (null === $currentKey) {
                    throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "]" at position %d.', $i));
                }

                if (!is_array($value) || !array_key_exists($currentKey, $value)) {
                    return $default;
                }

                $value = $value[$currentKey];
                $currentKey = null;
            } else {
                if (null === $currentKey) {
                    throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "%s" at position %d.', $char, $i));
                }

                $currentKey .= $char;
            }
        }

        if (null !== $currentKey) {
            throw new \InvalidArgumentException(sprintf('Malformed path. Path must end with "]".'));
        }

        return $value;
	}
}
