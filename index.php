<?php

declare(strict_types=1);

define('ROOT', __DIR__);

require_once ROOT . '/vendor/autoload.php';

use App\Request;
use Dotenv\Dotenv;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\NotFoundException;

Dotenv::createUnsafeImmutable(ROOT)->load(true);

ini_set('default_charset', getenv('DEFAULT_CHARSET'));
ini_set('display_errors', getenv('DEBUG'));
ini_set('error_reporting', E_ALL);

$request = Request::getInstance();
$router = include_once ROOT . '/src/routes.php';
$emitter = new SapiEmitter;

try {
    $emitter->emit($router->dispatch($request));
} catch (ForbiddenException $e) {
    $emitter->emit(new RedirectResponse('/erro403'));
} catch (NotFoundException $e) {
    $emitter->emit(new RedirectResponse('/erro404'));
} catch (Exception $e) {
    if (getenv('DEBUG')) {
        http_response_code(500);
        exit($e->getMessage());
    }

    $emitter->emit(new RedirectResponse('/erro500'));
}
