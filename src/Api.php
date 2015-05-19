<?php

namespace werx\Core;

use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Api.
 */
abstract class Api extends Controller
{
	public function ok($content)
	{
		return static::negotiateResponse($content, Response::HTTP_OK);
	}

	public function created($content, $uri_data)
	{
		return static::negotiateResponse($content, Response::HTTP_CREATED, ['Location'=> $uri_data]);
	}

	public function noContent()
	{
		return new Response(null, Response::HTTP_NO_CONTENT);
	}

	public function badRequest($message, $errors)
	{
		return static::negotiateResponse(['errors'=> $errors, 'message' => $message], Response::HTTP_BAD_REQUEST);
	}

	public function forbidden()
	{
		return new Response("403 Forbidden", Response::HTTP_FORBIDDEN);
	}

	public function unauthorized($www_atuthenticate = "Basic")
	{
		return new Response("401 Not Authorized", Response::HTTP_UNAUTHORIZED, ['WWW-Authenticate' => $www_atuthenticate]);
	}

	public function unprocessable($message, $errors)
	{
		return static::negotiateResponse(['errors'=> $errors, 'message' => $message], Response::HTTP_UNPROCESSABLE_ENTITY);
	}

	static $formats = [];
	static $negotiator = null;

	public static function init()
	{
		static::$negotiator = new \Negotiation\FormatNegotiator();

		static::registerFormat('json', function($content, $status_code, $headers) {
			return new JsonResponse($content, $status_code, $headers);
		});
	}

	public static function negotiateResponse($content, $status_code, array $headers = [], $accept = null)
	{
		if (empty($accept)) {
			$accept = $_SERVER["HTTP_ACCEPT"];
		}

		$negotiator = static::$negotiator;
		$available = static::availableContentTypes();

		$best = $negotiator->getBestFormat($accept, $available);

		if ( in_array($best, $available)) {
			$formatter = static::$formats[$best];
			return $formatter($content, $status_code, $headers);
		}

		return new Response(null, Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
	}

	public static function availableContentTypes()
	{
		return array_keys(static::$formats);
	}

	public static function registerFormat($format, \Closure $formatter, array $mime_types = [])
	{
		static::$formats[$format] = $formatter;
		if (!empty($mime_types)) {
			static::$negotiator->registerFormat($format, $mime_types, true);
		}
	}
}

Api::init();
