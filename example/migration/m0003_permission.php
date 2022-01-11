<?php

declare(strict_types=1);

use Anskh\PhpWeb\Db\Database;
use Anskh\PhpWeb\Db\Migration;
use Anskh\PhpWeb\Http\Auth\AccessControl;

/**
* Permission config when access conrol driver set to db
*
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
class m0003_permission extends Migration
{
    protected string $table = AccessControl::ATTR_PERMISSION;

    public function up(): bool
    {
        $type = $this->db->getType();
        $table = $this->db->getTable($this->table);
        
        if($type === Database::PGSQL){
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '( ' .
                $this->db->q('id') . ' serial,' .
                $this->db->q(AccessControl::ATTR_PERMISSION_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,
                PRIMARY KEY (' . $this->db->q('id') . '));';
        }elseif($type === Database::MYSQL){
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '('.
                $this->db->q('id') . ' INT NOT NULL AUTO_INCREMENT,' .
                $this->db->q(AccessControl::ATTR_PERMISSION_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,
                PRIMARY KEY (' . $this->db->q('id') . '))ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;';
        }elseif($type === Database::SQLITE){
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '('.
                $this->db->q('id') . ' INT NOT NULL AUTO_INCREMENT,' .
                $this->db->q(AccessControl::ATTR_PERMISSION_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,
                PRIMARY KEY (' . $this->db->q('id') . '));';
        }elseif($type === Database::SQLSRV){
            $sql = 'IF OBJECT_ID(\'' . $table . '\', \'U\') IS NULL CREATE TABLE ' . $table . '('.
                $this->db->q('id') . ' INT IDENTITY(1,1),' .
                $this->db->q(AccessControl::ATTR_PERMISSION_NAME) . ' VARCHAR(255) NOT NULL UNIQUE,
                PRIMARY KEY (' . $this->db->q('id') . '));';
        }

        try
        {
            $this->db->getConnection()->exec($sql);
        }catch(Exception $e){
            return false;
        }
         
        return true;
    }

    public function seed(): bool
    {
        $data = [
            [AccessControl::ATTR_PERMISSION_NAME => 'home'],
            [AccessControl::ATTR_PERMISSION_NAME => 'hello']
        ];

        try {
            if($this->db->insertBatch($data, $this->table) > 0) return true;
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}