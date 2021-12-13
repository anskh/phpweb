<?php

declare(strict_types=1);

namespace PhpWeb\Db;

use function PhpWeb\app;

abstract class Migration
{
    protected string $table;

    public abstract function up() : bool;

    public abstract function seed() : bool;

    public function down() : bool
    {
        $db = app()->db();
        $result = $db->connection()->exec("DROP TABLE IF EXISTS " . $db->table($this->table));

        return ($result === false) ? false: true;
    }
}