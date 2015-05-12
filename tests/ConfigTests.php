<?php

namespace werx\Core\Tests;

use werx\Core\WerxWebApp;

class ConfigTests extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		$_SERVER['SERVER_NAME'] = 'localhost';
		$_SERVER['SCRIPT_FILENAME'] = 'phpunit.php';
		$_SERVER['SCRIPT_NAME'] =  '/werx/phpunit.php';
		$_SERVER['REQUEST_URI'] = "/werx/phpunit.php";
		$this->config = new \werx\Core\Config((new WerxWebApp(['app_dir' => $this->getAppDir()]))->getContext());
	}

	public function testCanGetEnvironment()
	{
		$this->assertEquals('dev', $this->config->getEnvironment());
	}

	public function testCanResolvePath()
	{

		$this->assertEquals($this->getAppDir() . DIRECTORY_SEPARATOR . 'views', $this->config->resolvePath('views'));
	}

	public function testCanLoadDefaultConfig()
	{
		$this->config->load('default');
		$this->assertEquals('bar', $this->config->get('foo'));
	}

	public function testCanLoadEnvironmentConfig()
	{
		$this->config->load('envopts');
		$this->assertEquals('test', $this->config->get('env'));
	}

	public function testGetBaseUrlShouldReturnConfigItem()
	{
		$this->assertEquals('http://test.server.name/werx', $this->config->getBaseUrl());
	}

	public function testGetScriptUrlShouldReturnConfigItem()
	{
		$this->assertEquals('http://test.server.name/werx/phpunit.php', $this->config->getBaseUrl(true));
	}

	protected function getAppDir()
	{
		return __DIR__ .	DIRECTORY_SEPARATOR . 'resources';
	}
}
