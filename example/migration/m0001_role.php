<?php

declare(strict_types=1);

use PhpWeb\Config\Config;
use PhpWeb\Db\Migration;

use function PhpWeb\app;

class m0001_role extends Migration
{
    protected string $table = Config::ATTR_ACCESSCONTROL_ROLE;

    public function up(): bool
    {
        $db = app()->db($this->connection);
        $sql = 'CREATE TABLE IF NOT EXISTS ' . $db->table($this->table) . '(
            id INT(11) NOT NULL AUTO_INCREMENT,' .
            Config::ATTR_ACCESSCONTROL_ROLE_NAME . ' VARCHAR(255) NOT NULL UNIQUE,
            PRIMARY KEY (id)
        )ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;';

        try {
            $db->connection()->exec($sql);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function seed(): bool
    {
        $roles = app()->config(Config::ATTR_ACCESSCONTROL_CONFIG . '.' . Config::ATTR_ACCESSCONTROL_ROLE);
        $data = [];
        foreach($roles as $role)
        {
            $data[] = [Config::ATTR_ACCESSCONTROL_ROLE_NAME => $role];
        }

        try {
            app()->db($this->connection)->insert($data, $this->table);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
