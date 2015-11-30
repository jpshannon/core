<?php
namespace werx\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

/**
 * MessageFactory short summary.
 *
 */
interface MessageFactory
{
    /**
     * Create a response
     * 
     * @param string|resource|StreamInterface $body Message body, if any.
     * @param integer $status The status code of the response
     * @param array $headers Headers for the message, if any.
     * @return ResponseInterface
     */
    public function response($body = 'php://memory', $status = 200, array $headers = []);

	/**
	 * Create a response with the specified content
	 * 
	 * @param string|StreamInterface $content The content to be included in the response
	 * @param int $status The status code of the response
	 * @param array $headers Additional headers to be sent
	 * @return ResponseInterface
	 */
	public function content($content, $status = 200, array $headers = []);    

	/**
	 * Create a response intended to have no content
	 * 
	 * @param int $status The status code of the response
	 * @param array $headers Additional headers to be sent
	 * @return ResponseInterface
	 */
	public function noContent($status = 201, array $headers = []);

	/**
	 * Creats an encoded response
	 * 
	 * @param Encoder $encoder Encoder to encode content with
	 * @param mixed $content $content to be sent
	 * @param int $status HTTP Status Code
	 * @param array $headers Additional headers to be sent
	 * @return ResponseInterface
	 */
	public function encoded(Encoder $encoder, $content, $status = 200, array $headers = []);

	/**
	 * Create a json encoded response
	 * 
	 * @param mixed $content The content to be included in the response
	 * @param int $status The status code of the response
	 * @param array $headers Additional headers to be sent
	 * @param bool|string $callback The name of the callback to be called; false if jsonp is not used.
	 * @return ResponseInterface
	 */
	public function json($content, $status = 200, array $headers = [], $callback = false);

	/**
	 * Create a redirect response
	 * 
	 * @param string|UriInterface $location 
	 * @param bool $permanent 
	 * @return ResponseInterface
	 */
	public function redirect($location, $permanent=true);

    /**
     * Create a request from the supplied superglobal values.
     *
     * @param array $server $_SERVER superglobal
     * @param array $query $_GET superglobal
     * @param array $body $_POST superglobal
     * @param array $cookies $_COOKIE superglobal
     * @param array $files $_FILES superglobal
     * @return ServerRequestInterface
     */
    public function serverRequest(array $server = null, array $query = null, array $body = null, array $cookies = null, array $files = null);

    /**
     * Create a request
     * 
     * @param null|string $uri URI for the request, if any.
     * @param null|string $method HTTP method for the request, if any.
     * @param string|resource|StreamInterface $body Message body, if any.
     * @param array $headers Headers for the message, if any.
     * @return RequestInteface
     */
    public function request($uri = null, $method = null, $body = 'php://temp', array $headers = []);
}
