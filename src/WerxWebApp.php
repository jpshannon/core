<?php

namespace werx\Core;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * A base werx web application
 *
 */
class WerxWebApp extends WerxApp
{
	public function __construct(array $settings = [])
	{
		parent::__construct($settings);
        $base_path = $this->get('base_path');
        if (empty($base_path)) {
            $base_url = $this->getConfig('base_url');
            if ($base_url) { 
                $base_path = parse_url($base_url, PHP_URL_PATH);
            } else {
                $base_path = $_SERVER['SCRIPT_NAME'];
            }
            $this->set('base_path', $base_path);
        }
	}

    protected function loadMiddleware(Middleware $middlware)
    {
        return $middlware->addInitial(new Middleware\ResponseSender());
    }

	public function run()
	{
        $message = $this->getMessageFactory();
        $middleware = $this->middleware;
        return $middleware($message->serverRequest(), $message->content(''));
	}

	/**
	 * Get the directory views will be located in.
	 *
	 * @return string
	 */
	public function getViewsDir()
	{
		return $this->getAppResourcePath($this->getConfig('views_dir'));
	}
}
