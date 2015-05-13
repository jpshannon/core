<?php

namespace werx\Core\Tests;

use werx\Core\Tests\App\Controllers as Controllers;

class ControllerTests extends \PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		$this->app = new \werx\Core\Dispatcher(['app_dir'=>$this->getAppDir()]);
		ob_start();
	}

	public function __destruct()
	{
		echo ob_get_clean();
	}

	public function testBasicControllerAction()
	{
		$controller = new Controllers\Home($this->app->getContext());
		$controller->index();

		$this->expectOutputString('HOME\INDEX');
	}

	public function testCanOutputJson()
	{
		$controller = new Controllers\Home($this->app->getContext());
		$controller->json(['foo' => 'bar'])->send();

		$this->expectOutputString('{"foo":"bar"}');
	}

	public function testCanOutputJsonp()
	{
		$controller = new Controllers\Home($this->app->getContext());
		$controller->jsonp(['foo' => 'bar'])->send();

		$this->expectOutputString('/**/callback({"foo":"bar"});');
	}

	public function testCanRenderTemplate()
	{
		$controller = new Controllers\Home($this->app->getContext());
		$controller->renderTemplate();

		$this->expectOutputString('<p>bar</p>');
	}

	public function testCanOutputTemplate()
	{
		$controller = new Controllers\Home($this->app->getContext());
		$controller->outputTemplate()->send();

		$this->expectOutputString('<p>bar</p>');
	}

	public function testCanRedirectExternal()
	{
		$controller = new Controllers\Home($this->app->getContext());
		$controller->redirect('http://www.example.com')->send();

		$this->expectOutputRegex('/Redirecting to http:\/\/www.example.com/');
	}

	public function testCanRedirectLocal()
	{
		$controller = new Controllers\Home($this->app->getContext());
		$controller->redirect('home/people', 1)->send();

		$this->expectOutputRegex('/home\/people\/1/');
	}

	public function testCanRedirectLocalArray()
	{
		$controller = new Controllers\Home($this->app->getContext());
		$controller->redirect('home/people/{foo},{bar}', ['foo' => 'Foo', 'bar' => 'Bar'])->send();

		$this->expectOutputRegex('/home\/people\/Foo,Bar/');
	}

	public function testCanRedirectLocalQueryString()
	{
		$controller = new Controllers\Home($this->app->getContext());
		$controller->redirect('home/people', ['lastname' => 'Foo', 'firstname' => 'Bar'], true)->send();

		$this->expectOutputRegex('/home\/people\?lastname=Foo&amp;firstname=Bar/');
	}

	public function testCanExtendConsole()
	{
		$controller = new Controllers\Console($this->app->getContext());
		$controller->sayHello('Dave');
		$this->expectOutputString('Hello, Dave');
	}

	public function testCanPrefillFromSession()
	{
		$controller = new Controllers\Home($this->app->getContext());
		$controller->prefillFromSession()->send();
		$this->expectOutputString('from session', "Prefill in session should render the specified value");
	}

	public function testCanPrefillFromSessionDefaultValue()
	{
		$controller = new Controllers\Home($this->app->getContext());
		$controller->prefillFromSessionDefaultValue()->send();
		$this->expectOutputString('default', "No prefill in session should render default value");
	}

	protected function getAppDir()
	{
		return __DIR__ . DIRECTORY_SEPARATOR . 'resources';
	}
}
