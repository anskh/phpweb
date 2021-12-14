<?php

declare(strict_types=1);

use PhpWeb\Config\Config;
use PhpWeb\Db\Migration;

use function PhpWeb\app;

class m0004_assignment extends Migration
{
    protected string $table = Config::ATTR_ACCESSCONTROL_ASSIGNMENT;

    public function up(): bool
    {
        $db = app()->db($this->connection);
        $sql = 'CREATE TABLE IF NOT EXISTS ' . $db->table($this->table) . '(
            id INT(11) NOT NULL AUTO_INCREMENT, ' .
            Config::ATTR_ACCESSCONTROL_ROLE . ' VARCHAR(255) NOT NULL UNIQUE, ' .
            Config::ATTR_ACCESSCONTROL_PERMISSION . ' TEXT NOT NULL,
            PRIMARY KEY (id)
        )ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;';

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
        $assignments = app()->config(Config::ATTR_ACCESSCONTROL_CONFIG . '.' . Config::ATTR_ACCESSCONTROL_ASSIGNMENT);
        $data = [];
        foreach($assignments as $role => $permissions)
        {
            $data[] = [
                Config::ATTR_ACCESSCONTROL_ROLE => $role,
                Config::ATTR_ACCESSCONTROL_PERMISSION => $this->permissionString($permissions),
            ];
        }

        try {
            app()->db($this->connection)->insert($data, $this->table);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    private function permissionString(array $permissions): string
    {
        $string = '';
        foreach($permissions as $permission){
            if($string){
                $string .= Config::ACCESSCONTROL_SEPARATOR . $permission;
            }else{
                $string .= $permission;
            }
        }

        return $string;
    }
}