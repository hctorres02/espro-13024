<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DepartmentController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\UserController;
use App\Controllers\WarningController;
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

$router->group('/departamentos', function (RouteGroup $router) {
    $router->get('/', [DepartmentController::class, 'index']);
    $router->get('/cadastrar', [DepartmentController::class, 'create']);
    $router->post('/cadastrar', [DepartmentController::class, 'store']);
    $router->get('/{id:number}', [DepartmentController::class, 'edit']);
    $router->post('/{id:number}', [DepartmentController::class, 'update']);
    $router->get('/{id:number}/remover', [DepartmentController::class, 'destroy']);
})->middleware(new AuthMiddleware);

$router->group('/aprendizes', function (RouteGroup $router) {
    $router->get('/', [UserController::class, 'index']);
    $router->get('/cadastrar', [UserController::class, 'create']);
    $router->post('/cadastrar', [UserController::class, 'store']);
    $router->get('/{id:number}', [UserController::class, 'edit']);
    $router->post('/{id:number}', [UserController::class, 'update']);
    $router->get('/{id:number}/remover', [UserController::class, 'destroy']);
})->middleware(new AuthMiddleware);

$router->group('/postagens', function (RouteGroup $router) {
    $router->get('/', [PostController::class, 'index']);
    $router->get('/cadastrar', [PostController::class, 'create']);
    $router->post('/cadastrar', [PostController::class, 'store']);
    $router->get('/{id:number}', [PostController::class, 'edit']);
    $router->post('/{id:number}', [PostController::class, 'update']);
    $router->get('/{id:number}/remover', [PostController::class, 'destroy']);
})->middleware(new AuthMiddleware);

$router->group('/comunicados', function (RouteGroup $router) {
    $router->get('/', [WarningController::class, 'index']);
    $router->get('/cadastrar', [WarningController::class, 'create']);
    $router->post('/cadastrar', [WarningController::class, 'store']);
    $router->get('/{id:number}', [WarningController::class, 'edit']);
    $router->post('/{id:number}', [WarningController::class, 'update']);
    $router->get('/{id:number}/remover', [WarningController::class, 'destroy']);
})->middleware(new AuthMiddleware);

$router->get('/erro403', [ErrorController::class, 'error403']);
$router->get('/erro404', [ErrorController::class, 'error404']);
$router->get('/erro500', [ErrorController::class, 'error500']);

return $router;
