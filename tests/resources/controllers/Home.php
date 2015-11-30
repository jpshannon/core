<?php

namespace werx\Core\Tests\App\Controllers;

use werx\Core\Controller;

class Home extends Controller
{
	public function index()
	{
		return $this->content('HOME\INDEX');
	}

	public function outputTemplate()
	{
		return $this->view('.\foo', ['foo' => 'bar']);
	}
}
