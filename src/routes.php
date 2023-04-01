<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Middlewares\AuthMiddleware;
use App\Router;
use League\Route\RouteGroup;

$router = Router::getInstance();

$router->get('/', HomeController::class);

$router->group('/login', function (RouteGroup $router) {
    $router->get('/', [AuthController::class, 'index']);
    $router->post('/', [AuthController::class, 'store']);
});
$router->get('/logout', [AuthController::class, 'destroy']);

$router->get('/dashboard', DashboardController::class)->middleware(new AuthMiddleware);

$router->get('/erro403', [ErrorController::class, 'error403']);
$router->get('/erro404', [ErrorController::class, 'error404']);
$router->get('/erro500', [ErrorController::class, 'error500']);

return $router;
