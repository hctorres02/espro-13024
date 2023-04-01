<?php

declare(strict_types=1);

namespace App;

use App\Models\User;
use Josantonius\Session\Session as SessionManager;

class Session
{
    private static ?SessionManager $instance = null;

    private function __construct()
    {
        //
    }

    public static function getInstance(): SessionManager
    {
        if (self::$instance === null) {
            self::$instance = new SessionManager;
        }

        if (self::$instance->isStarted() == false) {
            self::$instance->start();
        }

        return self::$instance;
    }
}
