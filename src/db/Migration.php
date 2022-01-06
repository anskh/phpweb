<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Db;

use function Anskh\PhpWeb\app;

abstract class Migration
{
    protected string $table;
    protected string $connection;

    public function __construct(string $connection)
    {
        $this->connection = $connection;
    }

    public abstract function up() : bool;

    public abstract function seed() : bool;

    public function down() : bool
    {
        $db = app()->db($this->connection);
        $result = $db->connection()->exec("DROP TABLE IF EXISTS " . $db->table($this->table));

        return ($result === false) ? false: true;
    }
}