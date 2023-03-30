<?php

namespace App\Models;

class Warning extends Model
{
    protected string $table = 'warnings';

    public function homeWarnings()
    {
        return $this
            ->where([
                'warnings.status' => 'published',
                'warnings.published_at[<=]' => today(),
                'warnings.expires_at[>=]' => today(),
            ])
            ->join([
                '[><]departments' => ['department_id' => 'id'],
            ])
            ->select([
                'warning.title',
                'warning.body',
                'department' => [
                    'departments.id',
                    'departments.name',
                ],
            ]);
    }
}
