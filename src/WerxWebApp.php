<?php

namespace werx\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



/**
 * @property-read Request $request
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

	/**
	 * {@inheritdoc}
	 */
	public function getContext()
	{
		if ($this->context) {
			return $this->context;
		}
		return $this->context = new WebAppContext($this);
	}

	/**
	 * {@inheritdoc}
	 */
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
