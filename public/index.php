<?php

use App\Router\Router;
use App\Router\RouterException;

define('ROOT', dirname(__DIR__));

require_once ROOT . '/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$router = new Router($_GET['url']);

$router->get('/admin/test/:id/:slug', "Admin\Test\Test#index")->with('id', '[0-9]+')->with('slug', '[0-9\-a-z]+');
$router->get('/', "Home#index");

try {
    $router->run();
} catch (RouterException $e) {
}
