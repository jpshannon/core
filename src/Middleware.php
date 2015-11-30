<?php

namespace werx\Core;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Middleware
{
	protected $queue = [];

	public function __construct(array $queue = [])
	{
		$this->queue = $queue;
	}

	public function add(callable $callable)
	{
		$this->queue[] = $callable;
        return $this;
	}

	public function addInitial(callable $callable)
	{
		array_unshift($this->queue, $callable);
        return $this;
	}

    public function addAll(array $queue)
    {
        $this->queue = array_merge($queue);
        return $this;
    }

	public function __invoke(RequestInterface $request, ResponseInterface $response)
	{
		$middleware = array_shift($this->queue);
		return $middleware($request, $response, $this);
	}

}


