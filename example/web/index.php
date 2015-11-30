<?php
namespace werx\Example;


ini_set('display_errors', true);

// for example app only
$app_dir = dirname(dirname(__DIR__));

//if (file_exists('../src')) {
//    $app_dir = dirname(__DIR__);
//} else {
//    $app_dir = '/web/app-src/path_to_app';
//}

// Use Composer's autoloader.
require_once $app_dir . '/vendor/autoload.php';

$app = new ExampleApp();
$app->run();
