<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Medoo\Medoo;

/**
 * @property Medoo $db
 * @property string $table
 */
trait Update
{
    use Where;

    public function update(array $data, ?array $where = null): bool
    {
        return boolval($this->db->update($this->table, $data, $where));
    }
}
