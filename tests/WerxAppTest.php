<?php
namespace werx\Core\Tests;

use werx\Core\WerxWebApp;
use werx\Core\Context;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;
use werx\Core\Tests\App\Controllers as Controllers;

/**
 * WerxAppTest short summary.
 */
class WerxAppTest extends \PHPUnit_Framework_TestCase
{

	private function getApp()
	{
		return new WerxWebApp(['app_dir' =>  __DIR__ .	DIRECTORY_SEPARATOR . 'resources']);
	}

    /** @dataProvider resolvePathDataProvider */
    public function testResolvePath($basePath, $path, $expected)
    {
        $app = $this->getApp();

        $app->set('base_path', $basePath);
        $resolved = $app->resolvePath($path);

        $this->assertEquals($resolved, $expected);
    }

    public function resolvePathDataProvider()
    {
        return [
            ["/index.php", "/my/absolute/path", "/my/absolute/path"],
            ["/index.php", "~/", "/"],
            ["/index.php", "~/test.gif", "/test.gif"],
            ["/index.php", "controller/action", "/index.php/controller/action"],
            ["/web/index.php", "/my/absolute/path", "/my/absolute/path"],
            ["/web/index.php", "~/", "/web/"],
            ["/web/index.php", "~/test.gif", "/web/test.gif"],
            ["/web/index.php", "controller/action", "/web/index.php/controller/action"],
            ["/web/", "/my/absolute/path", "/my/absolute/path"],
            ["/web/", "~/", "/web/"],
            ["/web/", "~/test.gif", "/web/test.gif"],
            ["/web/", "controller/action", "/web/controller/action"],
            ["/web", "/my/absolute/path", "/my/absolute/path"],
            ["/web", "~/", "/web/"],
            ["/web", "~/test.gif", "/web/test.gif"],
            ["/web", "controller/action", "/web/controller/action"],
        ];
    }
    
}
