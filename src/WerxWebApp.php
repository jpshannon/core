<?php

namespace werx\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



/**
 * @property-read Request $request
 * @property-read WebAppContext $context
 */
class WerxWebApp extends WerxApp
{
	public function __construct($settings = [])
	{
		parent::__construct($settings);
		if (!isset($this['base_url'])) {
			$this['base_url'] = $this->request->getSchemeAndHttpHost();
		}
		$this['base_url'] = rtrim($this['base_url'], '/');
	}

	public function createContext()
	{
		return new WebAppContext($this);
	}

	protected function initServices(ServiceCollection $services)
	{
		parent::initServices($services);
		$services->setSingleton('request', function ($sc) {
			return Request::createFromGlobals();
		});
		return $services;
	}

	/**
	 * @param string $message
	 */
	public function pageNotFound($message = 'Not Found')
	{
		$response = new Response($message, 404, ['Content-Type' => 'text/plain']);
		$response->send();
	}
}
