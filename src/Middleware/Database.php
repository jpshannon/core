<?php

namespace werx\Core\Middleware;

use werx\Core\WerxApp;
use werx\Core\Database as DB;
use Psr\Http\Message\MessageInterface;

class Database
{
	protected $log_queries = false;

	/**
	 * @param string|array $dsn 
	 */
	public function __construct($dsn, $logQueries = false)
	{
		DB::init($dsn);
	}

	public function __invoke($request, $response, callable $next)
	{
		/**
		 * @var MessageInterface
		 */
		$response = $next($request, $response);
	
		if ($this->log_queries === true) {
			$body = $response->getBody();
			if ($body->isWritable()) {
				$body->write('<div class="query_log">');
				foreach(DB::getPrettyQueryLog() as $query) {
					$response->getBody()->write('<pre class="db_query">' .$query .'</pre>');
				}
				$body->write('</div>');
			}
		}

		return $response;
	}
}
