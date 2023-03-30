<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Medoo\Medoo;

/**
 * @property Medoo $db
 * @property string $table
 */
trait Unique
{
    use Select,
        Where;

    public function isUnique(string $column, $value, $id): bool
    {
        $this->where([$column => $value]);

        if ($id) {
            $this->where(['id[!]' => $id]);
        }

        return $this->count() == 0;
    }
}
