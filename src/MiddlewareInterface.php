<?php
namespace werx\Core;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * MiddlwareInterface short summary.
 */
interface MiddlewareInterface
{
	/**
	 * @param Request $request 
	 * @param Response $response 
	 * @param callable|MiddlewareInterface|null $next
	 * @return Response
	 */
	public function __invoke(Request $request, Response $response, callable $next);
}
