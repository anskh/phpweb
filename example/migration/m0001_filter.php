<?php

declare(strict_types=1);

use Anskh\PhpWeb\Db\Database;
use Anskh\PhpWeb\Db\Migration;
use Anskh\PhpWeb\Http\Auth\AccessControl;

/**
* Filter table for accesscontrol if driver is set to db
*
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/

class m0001_filter extends Migration
{
    protected string $table = AccessControl::ATTR_FILTER;

    public function up(): bool
    {
        $type = $this->db->getType();
        $table = $this->db->getTable($this->table);

        if ($type === Database::PGSQL) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $this->db->q('id') . ' serial,' .
                $this->db->q(AccessControl::ATTR_FILTER_TYPE) . ' VARCHAR(255) NOT NULL UNIQUE, ' .
                $this->db->q(AccessControl::ATTR_FILTER_LIST) . ' TEXT NULL,'.
                'PRIMARY KEY (' . $this->db->q('id') . '));';
        } elseif ($type === Database::MYSQL) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $this->db->q('id') . ' INT NOT NULL AUTO_INCREMENT,' .
                $this->db->q(AccessControl::ATTR_FILTER_TYPE) . ' VARCHAR(255) NOT NULL UNIQUE,' .
                $this->db->q(AccessControl::ATTR_FILTER_LIST) . ' TEXT NULL,'.
                'PRIMARY KEY (' . $this->db->q('id') . '))ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;';
        } elseif ($type === Database::SQLITE) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(' .
                $this->db->q('id') . ' INT NOT NULL AUTO_INCREMENT,' .
                $this->db->q(AccessControl::ATTR_FILTER_TYPE) . ' VARCHAR(255) NOT NULL UNIQUE, ' .
                $this->db->q(AccessControl::ATTR_FILTER_LIST) . ' TEXT NULL,'.
                'PRIMARY KEY (' . $this->db->q('id') . '));';
        } elseif ($type === Database::SQLSRV) {
            $sql = 'IF OBJECT_ID(\'' . $table . '\', \'U\') IS NULL CREATE TABLE ' . $table . '(' .
                $this->db->q('id') . ' INT IDENTITY,' .
                $this->db->q(AccessControl::ATTR_FILTER_TYPE) . ' VARCHAR(255) NOT NULL UNIQUE, ' .
                $this->db->q(AccessControl::ATTR_FILTER_LIST) . ' TEXT NULL,'.
                'PRIMARY KEY (' . $this->db->q('id') . '));';
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
                AccessControl::ATTR_FILTER_TYPE => AccessControl::FILTER_IP, 
                AccessControl::ATTR_FILTER_LIST => '10.0.0.*|8.8.8.8'
            ],
            [
                AccessControl::ATTR_FILTER_TYPE => AccessControl::FILTER_USER_AGENT, 
                AccessControl::ATTR_FILTER_LIST => null
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
