<?php

declare(strict_types=1);

define('ROOT', __DIR__);

require_once ROOT . '/vendor/autoload.php';

use App\Request;
use App\Session;
use Dotenv\Dotenv;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Http\Exception\NotFoundException;

Dotenv::createUnsafeImmutable(ROOT)->load(true);

ini_set('default_charset', getenv('DEFAULT_CHARSET'));
ini_set('display_errors', getenv('DEBUG'));
ini_set('error_reporting', E_ALL);

Session::revalidateAuth();

$request = Request::getInstance();
$router = include_once ROOT . '/src/routes.php';

try {
    $response = $router->dispatch($request);
} catch (NotFoundException $e) {
    header('location: /erro404');
    exit;
} catch (Exception $e) {
    http_response_code(500);

    if (getenv('DEBUG')) {
        exit(nl2br($e->getTraceAsString()));
    }

    exit('<pre>#500: Erro interno do servidor</pre>');
}

(new SapiEmitter)->emit($response);
