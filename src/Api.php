<?php
namespace werx\Core;

use Psr\Http\Message\ServerRequestInterface as Reqeust;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Api.
 */
abstract class Api extends Controller
{
	public function ok($content)
	{
		return $this->response->encoded($this->context->getEncoder(), $content);
	}

	public function created($content, $uri_data)
	{
		return $this->response->encoded($this->context->getEncoder(), $content, 201, ['Location'=> $uri_data]);
	}

	public function noContent()
	{
		return $this->response->noContent();
	}

	public function notFound($message = "Resource not found")
	{
		return $this->response->encoded($this->context->getEncoder(), $message, 404);
	}

	public function badRequest($message, $errors)
	{
        return $this->response->encoded($this->context->getEncoder(), ['errors'=> $errors, 'message' => $message], 400);;
	}

	public function forbidden()
	{
		return $this->context->getResponse()->withStatus(403);
	}

	public function unauthorized($www_authenticate = "Basic")
	{
        return $this->response->noContent(401, ['WWW-Authenticate' => $www_authenticate]);
	}

	public function unprocessable($message, $errors)
	{
        return $this->response->encoded($this->context->getEncoder(), ['errors'=> $errors, 'message' => $message], 422);
	}

}