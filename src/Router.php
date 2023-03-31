<?php

declare(strict_types=1);

namespace App;

use League\Route\Router as LeagueRouter;

class Router
{
    private static ?LeagueRouter $router = null;

    private function __construct()
    {
        //
    }

    public static function getInstance(): LeagueRouter
    {
        if (self::$router === null) {
            self::$router = new LeagueRouter;
        }

        return self::$router;
    }
}
