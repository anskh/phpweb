<?php

declare(strict_types=1);

use PhpWeb\Config\Config;
use PhpWeb\Db\Database;
use PhpWeb\Db\Migration;

use function PhpWeb\app;

class m0003_permission extends Migration
{
    protected string $table = Config::ATTR_ACCESSCONTROL_PERMISSION;

    public function up(): bool
    {
        $db = app()->db($this->connection);
        $type = $db->getDbType();
        $table = $db->table($this->table);
        
        if($type === Database::PGSQL){
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(
                id serial,' .
                Config::ATTR_ACCESSCONTROL_PERMISSION_NAME . ' VARCHAR(255) NOT NULL UNIQUE,
                PRIMARY KEY (id));';
        }elseif($type === Database::MYSQL){
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(
                id INT NOT NULL AUTO_INCREMENT,' .
                Config::ATTR_ACCESSCONTROL_PERMISSION_NAME . ' VARCHAR(255) NOT NULL UNIQUE,
                PRIMARY KEY (id))ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;';
        }elseif($type === Database::SQLITE){
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '(
                id INT NOT NULL AUTO_INCREMENT,' .
                Config::ATTR_ACCESSCONTROL_PERMISSION_NAME . ' VARCHAR(255) NOT NULL UNIQUE,
                PRIMARY KEY (id));';
        }elseif($type === Database::SQLSRV){
            $sql = 'IF OBJECT_ID(\'' . $table . '\', \'U\') IS NULL CREATE TABLE ' . $table . '(
                id INT IDENTITY(1,1),' .
                Config::ATTR_ACCESSCONTROL_PERMISSION_NAME . ' VARCHAR(255) NOT NULL UNIQUE,
                PRIMARY KEY (id));';
        }

        try
        {
            $db->connection()->exec($sql);
        }catch(Exception $e){
            return false;
        }
         
        return true;
    }

    public function seed(): bool
    {
        $permissions = app()->config(Config::ATTR_ACCESSCONTROL_CONFIG . '.' . Config::ATTR_ACCESSCONTROL_PERMISSION);
        $data = [];
        foreach($permissions as $permission)
        {
            $data[] = [Config::ATTR_ACCESSCONTROL_PERMISSION_NAME => $permission];
        }

        try {
            if(app()->db($this->connection)->insert($data, $this->table) > 0) return true;
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}