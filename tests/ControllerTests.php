<?php

namespace werx\Core\Tests;

use werx\Core\WerxWebApp;
use werx\Core\Context;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;
use werx\Core\Tests\App\Controllers as Controllers;

class ControllerTests extends \PHPUnit_Framework_TestCase
{

	private function getContext($controller = 'home', $action = 'index', $args = [])
	{
		$app = new WerxWebApp(['app_dir' =>  __DIR__ .	DIRECTORY_SEPARATOR . 'resources']);
		$context = new Context($app, $controller, $action, $args);
		$context->setRequest(ServerRequestFactory::fromGlobals());
		$context->setResponse(new Response());
		return $context;
	}

	public function testBasicControllerAction()
	{
        $controller = new Controllers\Home($this->getContext());
		$response = $controller->index();

		$this->assertEquals((string)$response->getBody(), 'HOME\INDEX');
	}

	public function testCanOutputTemplate()
	{
		$controller = new Controllers\Home($this->getContext());
		$response = $controller->outputTemplate();

		$this->assertEquals((string)$response->getBody(), '<p>bar</p>');
	}

	public function testCanRedirectExternal()
	{
		$controller = new Controllers\Home($this->getContext());
		$response = $controller->redirect('http://www.example.com');

		$this->assertTrue($response->hasHeader('Location'));
		$this->assertEquals('http://www.example.com', $response->getHeaderLine('location'));
	}

	public function testCanRedirectLocal()
	{
		$controller = new Controllers\Home($this->getContext());
		$response = $controller->redirect('home/people', 1);

		$this->assertEquals('/home/people/1', $response->getHeaderLine('location'));
	}

	public function testCanRedirectLocalArray()
	{
		$controller = new Controllers\Home($this->getContext());
		$response = $controller->redirect('home/people/{foo},{bar}', ['foo' => 'Foo', 'bar' => 'Bar']);
        $this->assertEquals('/home/people/Foo,Bar', $response->getHeaderLine('location'));
	}

	public function testCanRedirectLocalQueryString()
	{
		$controller = new Controllers\Home($this->getContext());
		$response = $controller->redirect('home/people', ['lastname' => 'Foo', 'firstname' => 'Bar'], true);
        $this->assertEquals('/home/people?lastname=Foo&firstname=Bar', $response->getHeaderLine('location'));
	}

	public function testCanExtendConsole()
	{
		$controller = new Controllers\Console($this->getContext());
		$controller->sayHello('Dave');
		$this->expectOutputString('Hello, Dave');
	}

	protected function getAppDir()
	{
		return __DIR__ . DIRECTORY_SEPARATOR . 'resources';
	}
}
