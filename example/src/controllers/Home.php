<?php
namespace werx\Example\Controllers;

use werx\Core\Controller;

/**
 * Home short summary.
 */
class Home extends Controller
{
    protected function initializeTemplate()
    {
        $template = parent::initializeTemplate();
        $template->layout('layouts/default', ['page_title' => "Werx Example site"]);
        return $template;
    }

    public function index()
    {
        return $this->view();
    }
}
