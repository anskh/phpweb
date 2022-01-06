<?php

declare(strict_types=1);

use Anskh\PhpWeb\Config\Config;
use Anskh\PhpWeb\Db\Database;
use Anskh\PhpWeb\Db\Migration;

class m0004_assignment extends Migration
{
    protected string $table = Config::ATTR_ACCESSCONTROL_ASSIGNMENT;

    public function up(): bool
    {
        $db = my_app()->db($this->connection);
        $type = $db->getDbType();
        $table = $db->table($this->table);

        if ($type === Database::PGSQL) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $db->quoteAttribute('id') . ' serial, ' .
                $db->quoteAttribute(Config::ATTR_ACCESSCONTROL_ROLE) . ' VARCHAR(255) NOT NULL UNIQUE, ' .
                $db->quoteAttribute(Config::ATTR_ACCESSCONTROL_PERMISSION) . ' TEXT NULL,'.
                'PRIMARY KEY (' . $db->quoteAttribute('id') . '));';
        } elseif ($type === Database::MYSQL) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $db->quoteAttribute('id') . ' INT NOT NULL AUTO_INCREMENT, ' .
                $db->quoteAttribute(Config::ATTR_ACCESSCONTROL_ROLE) . ' VARCHAR(255) NOT NULL UNIQUE, ' .
                $db->quoteAttribute(Config::ATTR_ACCESSCONTROL_PERMISSION) . ' TEXT NULL,'.
                'PRIMARY KEY (' . $db->quoteAttribute('id') . '))ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;';
        } elseif ($type === Database::SQLITE) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $db->quoteAttribute('id') . ' INT NOT NULL AUTO_INCREMENT, ' .
                $db->quoteAttribute(Config::ATTR_ACCESSCONTROL_ROLE) . ' VARCHAR(255) NOT NULL UNIQUE, ' .
                $db->quoteAttribute(Config::ATTR_ACCESSCONTROL_PERMISSION) . ' TEXT NULL,'.
                'PRIMARY KEY (' . $db->quoteAttribute('id') . '));';
        } elseif ($type === Database::SQLSRV) {
            $sql = 'IF OBJECT_ID(\'' . $table . '\', \'U\') IS NULL CREATE TABLE ' . $table . '(' .
                $db->quoteAttribute('id') . ' INT IDENTITY(1,1), ' .
                $db->quoteAttribute(Config::ATTR_ACCESSCONTROL_ROLE) . ' VARCHAR(255) NOT NULL UNIQUE, ' .
                $db->quoteAttribute(Config::ATTR_ACCESSCONTROL_PERMISSION) . ' TEXT NULL,'.
                'PRIMARY KEY (' . $db->quoteAttribute('id') . '));';
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
        $assignments = my_app()->config(Config::ATTR_ACCESSCONTROL_CONFIG . '.' . Config::ATTR_ACCESSCONTROL_ASSIGNMENT, []);
        $data = [];
        foreach ($assignments as $role => $permissions) {
            $data[] = [
                Config::ATTR_ACCESSCONTROL_ROLE => $role,
                Config::ATTR_ACCESSCONTROL_PERMISSION => empty($permissions) ? null : implode(Config::ACCESSCONTROL_SEPARATOR, $permissions),
            ];
        }
        
        try {
            if (my_app()->db($this->connection)->insert($data, $this->table) > 0) return true;
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}