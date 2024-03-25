<?php

use App\Controller\ErrorController;
use App\Router\Router;
use App\Router\RouterException;


define('ROOT', dirname(__DIR__));

require_once ROOT . '/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(ROOT, '.env.local');
$dotenv->safeLoad();

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(ROOT);
$dotenv->load();

$router = new Router($_GET['url']);

$router->get('/', "Home#index");
$router->get('/login', "Security#login");
$router->get('/logout', 'Security#logout');
$router->get('/register', 'Register#register');
$router->get('/verify-email', 'Register#verifyEmail');
$router->get('/resend-verify-email/:slug', 'Register#resendVerifyEmail')->with('slug', '[0-9\-a-z]+');
$router->get('/reset-password/index', 'ResetPassword#indexResetPassword');
$router->get('/reset-password/confirm-send', 'ResetPassword#confirmSend');
$router->get('/reset-password/verify-token/', 'ResetPassword#verifyResetPasswordToken');
$router->get('/reset-password', 'ResetPassword#resetPassword');
$router->get('/profil', 'User\User#index');
$router->get('/profil/edit-password', 'User\User#editPassword');
$router->get('/admin', 'Admin\Admin#index');
$router->get('/admin/user/edit/:slug', "Admin\User#edit")->with('slug', '[0-9\-a-z]+');
$router->get('/admin/user', "Admin\User#index");
$router->get('/admin/test/:id/:slug', "Admin\Test\Test#index")->with('id', '[0-9]+')->with('slug', '[0-9\-a-z]+');

$router->post('/login', 'Security#handleLoginSubmit');
$router->post('/register', 'Register#handleRegisterSubmit');
$router->post('/reset-password/index', 'ResetPassword#handleResetPasswordSubmit');
$router->post('/reset-password', 'ResetPassword#resetPassword');
$router->post('/profil', 'User\User#index');
$router->post('/profil/edit-password', 'User\User#editPassword');
$router->post('/admin/user/edit/:slug', "Admin\User#edit")->with('slug', '[0-9\-a-z]+');
$router->post('/admin/user/delete/:slug', "Admin\User#delete")->with('slug', '[0-9\-a-z]+');


try {
    $router->run();
} catch (RouterException $e) {
    
    $controller = new ErrorController();
    $response = $controller->renderError($e->getCode(), $e->getMessage());
    
    $response->send();
    
    return;
}
