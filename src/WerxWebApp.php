<?php

namespace werx\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



/**
 * A base werx web application
 *
 * @property-read Request $request
 * @property-read \werx\Config\Container $config
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

	public function run()
	{
		$response = parent::run();
		if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
			$response->send();
		} else {
            if ($response = $this->service->get('response',false)) {
                $response->send();
            } else {
                $this->pageNotFound()->send();
            }
		}
	}

	/**
	 * @param string $message
	 */
	public function pageNotFound($message = 'Not Found', $content_type = "text/plain")
	{
		return new Response($message, 404, ['Content-Type' => $content_type]);
	}
}
