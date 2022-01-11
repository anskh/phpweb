<?php

declare(strict_types=1);

use Anskh\PhpWeb\Db\Database;
use Anskh\PhpWeb\Db\Migration;
use Anskh\PhpWeb\Http\Session\Session;
use Anskh\PhpWeb\Model\User;

/**
* description
*
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
class m0005_user extends Migration
{
    protected string $table = User::ATTR_TABLE;

    public function up(): bool
    {
        $type = $this->db->getType();
        $table = $this->db->getTable($this->table);

        if ($type === Database::PGSQL) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $this->db->q(User::ATTR_ID) . ' serial,' .
                $this->db->q(User::ATTR_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,' .
                $this->db->q(User::ATTR_PASSWORD) . ' VARCHAR(255) NOT NULL,' .
                $this->db->q(User::ATTR_TOKEN) . ' VARCHAR(255) NOT NULL DEFAULT \'\',' .
                $this->db->q(User::ATTR_ROLES) . ' VARCHAR(255) NOT NULL DEFAULT \'\',
                PRIMARY KEY (' . $this->db->q(User::ATTR_ID) . '));';
        } elseif ($type === Database::MYSQL) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $this->db->q(User::ATTR_ID) . ' INT(11) NOT NULL AUTO_INCREMENT,' .
                $this->db->q(User::ATTR_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,' .
                $this->db->q(User::ATTR_PASSWORD) . ' VARCHAR(255) NOT NULL,' .
                $this->db->q(User::ATTR_TOKEN) . ' VARCHAR(255) NOT NULL DEFAULT \'\',' .
                $this->db->q(User::ATTR_ROLES) . ' VARCHAR(255) NOT NULL DEFAULT \'\',
                PRIMARY KEY (' . $this->db->q(User::ATTR_ID) . '))ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;';
        } elseif ($type === Database::SQLITE) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $this->db->q(User::ATTR_ID) . ' INT(11) NOT NULL AUTO_INCREMENT,' .
                $this->db->q(User::ATTR_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,' .
                $this->db->q(User::ATTR_PASSWORD) . ' VARCHAR(255) NOT NULL,' .
                $this->db->q(User::ATTR_TOKEN) . ' VARCHAR(255) NOT NULL DEFAULT \'\',' .
                $this->db->q(User::ATTR_ROLES) . ' VARCHAR(255) NOT NULL DEFAULT \'\',
                PRIMARY KEY (' . $this->db->q(User::ATTR_ID) . '));';
        } elseif ($type === Database::SQLSRV) {
            $sql = 'IF OBJECT_ID(\'' . $table . '\', \'U\') IS NULL CREATE TABLE ' . $table . '(' .
                $this->db->q(User::ATTR_ID) . ' INT IDENTITY,' .
                $this->db->q(User::ATTR_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,' .
                $this->db->q(User::ATTR_PASSWORD) . ' VARCHAR(255) NOT NULL,' .
                $this->db->q(User::ATTR_TOKEN) . ' VARCHAR(255) NOT NULL DEFAULT \'\',' .
                $this->db->q(User::ATTR_ROLES) . ' VARCHAR(255) NOT NULL DEFAULT \'\',
                PRIMARY KEY (' . $this->db->q(User::ATTR_ID) . '));';
        }

        try {
            $this->db->getConnection()->exec($sql);
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
                'password' => password_hash('123', PASSWORD_BCRYPT),
                'token' => Session::generateToken(),
                'roles' => 'user'
            ],
            [
                'name' => 'admin',
                'password' => password_hash('123', PASSWORD_BCRYPT),
                'token' => Session::generateToken(),
                'roles' => 'admin|user'
            ]
        ];

        try {
            if ($this->db->insertBatch($data, $this->table) > 0) return true;
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}
