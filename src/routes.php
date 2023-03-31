<?php

declare(strict_types=1);

use App\Controllers\ErrorController;
use App\Router;

$router = Router::getInstance();

$router->get('/erro403', [ErrorController::class, 'error403']);
$router->get('/erro404', [ErrorController::class, 'error404']);
$router->get('/erro500', [ErrorController::class, 'error500']);

return $router;
