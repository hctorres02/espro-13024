<?php

namespace App\Models\Concerns;

use Medoo\Medoo;

/**
 * @property Medoo $db
 * @property string $table
 */
trait Select
{
    public function get(string|array $columns = '*')
    {
        if ($this->join) {
            return $this->db->get($this->table, $this->join, $columns, $this->where);
        }

        return $this->db->get($this->table, $columns, $this->where);
    }

    public function select(string|array $columns = '*')
    {
        if ($this->join) {
            return $this->db->select($this->table, $this->join, $columns, $this->where);
        }

        return $this->db->select($this->table, $columns, $this->where);
    }

    public function count(string $column = '*')
    {
        if ($this->join) {
            return $this->db->count($this->table, $this->join, $column, $this->where);
        }

        return $this->db->count($this->table, $this->where);
    }
}
