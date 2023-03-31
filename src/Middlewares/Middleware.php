<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Session;
use Josantonius\Session\Session as SessionManager;

class Middleware
{
    protected SessionManager $session;

    public function __construct()
    {
        $this->session = Session::getInstance();
    }
}
