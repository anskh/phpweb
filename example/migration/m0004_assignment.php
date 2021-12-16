<?php

declare(strict_types=1);

use PhpWeb\Config\Config;
use PhpWeb\Db\Database;
use PhpWeb\Db\Migration;

use function PhpWeb\app;

class m0004_assignment extends Migration
{
    protected string $table = Config::ATTR_ACCESSCONTROL_ASSIGNMENT;

    public function up(): bool
    {
        $db = app()->db($this->connection);
        $type = $db->getDbType();
        $table = $db->table($this->table);

        if ($type === Database::PGSQL) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(
                id serial, ' .
                Config::ATTR_ACCESSCONTROL_ROLE . ' VARCHAR(255) NOT NULL UNIQUE, ' .
                Config::ATTR_ACCESSCONTROL_PERMISSION . ' TEXT NOT NULL,
                PRIMARY KEY (id));';
        } elseif ($type === Database::MYSQL) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(
                id INT NOT NULL AUTO_INCREMENT, ' .
                Config::ATTR_ACCESSCONTROL_ROLE . ' VARCHAR(255) NOT NULL UNIQUE, ' .
                Config::ATTR_ACCESSCONTROL_PERMISSION . ' TEXT NOT NULL,
                PRIMARY KEY (id))ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;';
        } elseif ($type === Database::SQLITE) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(
                id INT NOT NULL AUTO_INCREMENT, ' .
                Config::ATTR_ACCESSCONTROL_ROLE . ' VARCHAR(255) NOT NULL UNIQUE, ' .
                Config::ATTR_ACCESSCONTROL_PERMISSION . ' TEXT NOT NULL,
                PRIMARY KEY (id));';
        } elseif ($type === Database::SQLSRV) {
            $sql = 'IF OBJECT_ID(\'' . $table . '\', \'U\') IS NULL CREATE TABLE ' . $table . '(
                id INT IDENTITY(1,1), ' .
                Config::ATTR_ACCESSCONTROL_ROLE . ' VARCHAR(255) NOT NULL UNIQUE, ' .
                Config::ATTR_ACCESSCONTROL_PERMISSION . ' TEXT NOT NULL,
                PRIMARY KEY (id));';
        }

        try {
            $db->connection()->exec($sql);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function seed(): bool
    {
        $assignments = app()->config(Config::ATTR_ACCESSCONTROL_CONFIG . '.' . Config::ATTR_ACCESSCONTROL_ASSIGNMENT);
        $data = [];
        foreach ($assignments as $role => $permissions) {
            $data[] = [
                Config::ATTR_ACCESSCONTROL_ROLE => $role,
                Config::ATTR_ACCESSCONTROL_PERMISSION => $this->permissionString($permissions),
            ];
        }

        try {
            if (app()->db($this->connection)->insert($data, $this->table) > 0) return true;
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    private function permissionString(array $permissions): string
    {
        $string = '';
        foreach ($permissions as $permission) {
            if ($string) {
                $string .= Config::ACCESSCONTROL_SEPARATOR . $permission;
            } else {
                $string .= $permission;
            }
        }

        return $string;
    }
}
