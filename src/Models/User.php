<?php

declare(strict_types=1);

namespace App\Models;

class User extends Model
{
    protected string $table = 'users';

    public static array $protected = [
        'password',
    ];

    public function homeBirthdays()
    {
        return $this
            ->where([
                'users.status' => true,
            ])
            ->whereDateBetween('birth_date', 30)
            ->join([
                '[>]departments' => ['department_id' => 'id'],
            ])
            ->select([
                'users.name',
                'department' => [
                    'departments.name'
                ],
            ]);
    }
}
