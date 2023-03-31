<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Medoo\Medoo;

/**
 * @property Medoo $db
 */
trait Insert
{
    protected string $table;

    public function insert(array $values): ?string
    {
        $this->db->insert($this->table, $values);

        return $this->db->id();
    }
}
