<?php

namespace App\Models\Concerns;

/**
 * @property Medoo $db
 * @property string $table
 */
trait Join
{
    private array $join = [];

    public function join(array $relations): static
    {
        $this->join = array_merge($this->join, $relations);

        return $this;
    }
}
