<?php

namespace App\Models;

class Department extends Model
{
    protected string $table = 'departments';

    public function homeDepartments()
    {
        return $this
            ->where([
                'status' => true,
            ])
            ->orderBy('name')
            ->select();
    }
}
