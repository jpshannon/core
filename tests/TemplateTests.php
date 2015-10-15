<?php

namespace werx\Core\Tests;

use werx\Core\ViewEngine;

class TemplateTests extends \PHPUnit_Framework_TestCase
{
	public function testEscapeViewVars()
	{
		$template = new ViewEngine(__DIR__ . '/resources/views/');

		$template->layout('layouts/default');

		$output = $template->render('foo', ['foo' => '<foo>', 'xss' => '<script>alert("xss")</script>']);

		$this->assertRegExp('/&lt;foo&gt;/', $output);
	}

	public function testUnguardViewVars()
	{
		$template = new ViewEngine(__DIR__ . '/resources/views/');

		$template->layout('layouts/default');
		$template->unguard('foo');

		$output = $template->render('foo', ['foo' => '<foo>']);

		$this->assertRegExp('/\<foo\>/', $output);
	}
}
