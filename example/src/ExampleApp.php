<?php
namespace werx\Example;

use werx\Core\WerxWebApp;
use werx\Core\Middleware;
use werx\Core\Middleware\AuraRoutes;

/**
 * ExampleApp short summary.
 */
class ExampleApp extends WerxWebApp
{
    public function __construct(array $settings = array())
    {
        parent::__construct($settings);
    }

    protected function loadMiddleware(Middleware $middleware)
    {
        return parent::loadMiddleware($middleware)
            ->add(new AuraRoutes($this));
    }
}
