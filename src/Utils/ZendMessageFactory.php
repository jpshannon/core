<?php
namespace werx\Core\Utils;

use werx\Core\MessageFactory;
use werx\Core\Encoders;
use Zend\Diactoros\Response;
use Zend\Diactoros\Request;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * ZendMessageFactory short summary.
 */
class ZendMessageFactory implements MessageFactory
{
    public function response($body = 'php://memory', $status = 200, array $headers = [])
    {
        return new Response($body, $status, $headers);
    }

    public function content($content, $status = 200, array $headers = [])
	{
		return new HtmlResponse($content, $status, $headers);
	}

	public function noContent($status = 201, array $headers = [])
	{
		return new EmptyResponse($status, $headers);
	}

	public function json($content, $status = 200, array $headers = [], $callback = false)
	{
		$encoder = new Encoders\JsonEncoder();
		if ($callback) {
			$encoder = new Encoders\JsonpEncoder($callback);
		}
		return $this->encoded($encoder, $content, $status, $headers);
	}

	public function encoded(\werx\Core\Encoder $encoder, $content, $status = 200, array $headers = array())	
	{
		$headers['Content-Type'] = $encoder->getMimeType();
		$response = new Response('php://temp', $status, $headers);
		$response->getBody()->write($encoder->encode($content));
		return $response;
	}

	public function redirect($location, $permanent=true)
	{
		$status = $permanent !== true ? 302 : 301;
		return new RedirectResponse($location, $status);
	}

    public function serverRequest(array $server = null, array $query = null, array $body = null, array $cookies = null, array $files = null)
    {
        return ServerRequestFactory::fromGlobals($server, $query, $body, $cookies, $files);
    }

    public function request($uri = null, $method = null, $body = 'php://temp', array $headers = [])
    {
        return new Request($uri, $method, $body, $headers);
    }

	
}
