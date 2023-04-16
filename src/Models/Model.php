<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Insert;
use App\Models\Concerns\Join;
use App\Models\Concerns\Select;
use App\Models\Concerns\Unique;
use App\Models\Concerns\Update;
use App\Models\Concerns\Where;
use Medoo\Medoo;
use PDO;

class Model
{
    use Select,
        Insert,
        Update,
        Join,
        Where,
        Unique;

    private Medoo $db;

    public function __construct()
    {
        $host = getenv('DB_HOST');
        $database = getenv('DB_NAME');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');
        $charset = getenv('DB_CHARSET');

        $this->db = new Medoo([
            'type' => 'mysql',
            'host' => $host,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => $charset,
            'error' => PDO::ERRMODE_WARNING,
        ]);
    }

    public static function newQuery(): self
    {
        return new static;
    }

    public static function raw(string $string, array $map = [])
    {
        return Medoo::raw($string, $map);
    }

    public function groupBy(string|array $group)
    {
        return $this->where(['GROUP' => $group]);
    }

    public function orderBy(string|array $order)
    {
        return $this->where(['ORDER' => $order]);
    }
}
