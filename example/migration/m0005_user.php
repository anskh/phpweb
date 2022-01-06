<?php

declare(strict_types=1);

use Anskh\PhpWeb\Config\Config;
use Anskh\PhpWeb\Db\Database;
use Anskh\PhpWeb\Db\Migration;
use Anskh\PhpWeb\Http\Session\Session;
use Anskh\PhpWeb\Model\User;

class m0005_user extends Migration
{
    protected string $table = User::ATTR_TABLE;

    public function up(): bool
    {
        $db = my_app()->db($this->connection);
        $type = $db->getDbType();
        $table = $db->table($this->table);

        if ($type === Database::PGSQL) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $db->quoteAttribute(User::ATTR_ID) . ' serial,' .
                $db->quoteAttribute(User::ATTR_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,' .
                $db->quoteAttribute(User::ATTR_PASSWORD) . ' VARCHAR(255) NOT NULL,' .
                $db->quoteAttribute(User::ATTR_TOKEN) . ' VARCHAR(255) NOT NULL DEFAULT \'\',' .
                $db->quoteAttribute(User::ATTR_ROLES) . ' VARCHAR(255) NOT NULL DEFAULT \'\',
                PRIMARY KEY (' . $db->quoteAttribute(User::ATTR_ID) . '));';
        } elseif ($type === Database::MYSQL) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $db->quoteAttribute(User::ATTR_ID) . ' INT(11) NOT NULL AUTO_INCREMENT,' .
                $db->quoteAttribute(User::ATTR_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,' .
                $db->quoteAttribute(User::ATTR_PASSWORD) . ' VARCHAR(255) NOT NULL,' .
                $db->quoteAttribute(User::ATTR_TOKEN) . ' VARCHAR(255) NOT NULL DEFAULT \'\',' .
                $db->quoteAttribute(User::ATTR_ROLES) . ' VARCHAR(255) NOT NULL DEFAULT \'\',
                PRIMARY KEY (' . $db->quoteAttribute(User::ATTR_ID) . '))ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;';
        } elseif ($type === Database::SQLITE) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $db->quoteAttribute(User::ATTR_ID) . ' INT(11) NOT NULL AUTO_INCREMENT,' .
                $db->quoteAttribute(User::ATTR_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,' .
                $db->quoteAttribute(User::ATTR_PASSWORD) . ' VARCHAR(255) NOT NULL,' .
                $db->quoteAttribute(User::ATTR_TOKEN) . ' VARCHAR(255) NOT NULL DEFAULT \'\',' .
                $db->quoteAttribute(User::ATTR_ROLES) . ' VARCHAR(255) NOT NULL DEFAULT \'\',
                PRIMARY KEY (' . $db->quoteAttribute(User::ATTR_ID) . '));';
        } elseif ($type === Database::SQLSRV) {
            $sql = 'IF OBJECT_ID(\'' . $table . '\', \'U\') IS NULL CREATE TABLE ' . $table . '(' .
                $db->quoteAttribute(User::ATTR_ID) . ' INT IDENTITY,' .
                $db->quoteAttribute(User::ATTR_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,' .
                $db->quoteAttribute(User::ATTR_PASSWORD) . ' VARCHAR(255) NOT NULL,' .
                $db->quoteAttribute(User::ATTR_TOKEN) . ' VARCHAR(255) NOT NULL DEFAULT \'\',' .
                $db->quoteAttribute(User::ATTR_ROLES) . ' VARCHAR(255) NOT NULL DEFAULT \'\',
                PRIMARY KEY (' . $db->quoteAttribute(User::ATTR_ID) . '));';
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
        $data = [
            [
                'name' => 'user',
                'password' => password_hash('123', Config::HASHING_ALGORITHM),
                'token' => Session::generateToken(),
                'roles' => 'user'
            ],
            [
                'name' => 'admin',
                'password' => password_hash('123', Config::HASHING_ALGORITHM),
                'token' => Session::generateToken(),
                'roles' => 'admin|user'
            ]
        ];

        try {
            if (my_app()->db($this->connection)->insert($data, $this->table) > 0) return true;
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}
