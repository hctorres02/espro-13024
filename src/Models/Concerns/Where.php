<?php

namespace App\Models\Concerns;

/**
 * @property string $table
 */
trait Where
{
    private array $where = [];

    public function where(array $clauses): static
    {
        $this->where = array_merge($this->where, $clauses);

        return $this;
    }

    public function whereDateBetween(string $column, int $days, string $starts_at = null)
    {
        if ($starts_at === null) {
            $starts_at = today();
        }

        return $this->where([
            "{$this->table}.{$column}[<>]" => [
                $starts_at,
                today("{$days} days")
            ]
        ]);
    }
}
