<?php

declare(strict_types=1);

namespace App\Models;

class User extends Model
{
    protected string $table = 'users';

    public static array $protected = [
        'password',
    ];
}
