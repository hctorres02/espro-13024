<?php

declare(strict_types=1);

namespace App;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Router as LeagueRouter;

class Router
{
    private ServerRequest $request;

    private LeagueRouter $router;

    private SapiEmitter $emitter;

    public function __construct()
    {
        $this->router = new LeagueRouter;
        $this->emitter = new SapiEmitter;
        $this->request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    }

    public function dispatch()
    {
        $response = $this->router->dispatch($this->request);

        $this->emitter->emit($response);
    }

    public function get(string $path, $handler)
    {
        return $this->router->map('GET', $path, $handler);
    }

    public function post(string $path, $handler)
    {
        return $this->router->map('POST', $path, $handler);
    }
}
