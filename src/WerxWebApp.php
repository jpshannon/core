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

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$response = parent::run();
		if ($override_response = $this->getResponse()) {
			$override_response->send();
		} elseif ($response instanceof \Symfony\Component\HttpFoundation\Response) {
			$response->send();
		} else {
			$this->pageNotFound()->send();
		}
	}

	/**
	 * Set Response to be sent for the current request
	 *
	 * Use this to override any standard response the application might normally use.
	 * 
	 * @param Response $response
	 * @return WerxWebApp
	 */
	public function setResponse(Response $response)
	{
		$this->services->set('response', $response, true);
		return $this;
	}

	/**
	 * Get Response to be sent
	 *
	 * If set, the application will use the Response instead of the response from the module pipeline 
	 * 
	 * @return Respnose|bool
	 */
	public function getResponse()
	{
		return $this->services->get('response', false);
	}

	/**
	 * Generate a 404 response
	 * 
	 * @param string $message
	 * @return Response
	 */
	public function pageNotFound($message = 'Not Found', $content_type = "text/plain")
	{
		return new Response($message, 404, ['Content-Type' => $content_type]);
	}
}
