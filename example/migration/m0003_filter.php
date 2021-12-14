<?php

declare(strict_types=1);

use PhpWeb\Config\Config;
use PhpWeb\Db\Migration;

use function PhpWeb\app;

class m0003_filter extends Migration
{
    protected string $table = Config::ATTR_ACCESSCONTROL_FILTER;

    public function up(): bool
    {
        $db = app()->db($this->connection);
        $sql = 'CREATE TABLE IF NOT EXISTS ' . $db->table($this->table) . '(
            id INT(11) NOT NULL AUTO_INCREMENT,' .
            Config::ATTR_ACCESSCONTROL_FILTER_TYPE . ' VARCHAR(255) NOT NULL UNIQUE, ' .
            Config::ATTR_ACCESSCONTROL_FILTER_LIST . ' TEXT NOT NULL DEFAULT \'\',
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
        $data = [
            [
                Config::ATTR_ACCESSCONTROL_FILTER_TYPE => Config::ACCESSCONTROL_FILTER_IP,
                Config::ATTR_ACCESSCONTROL_FILTER_LIST => null
            ],
            [
                Config::ATTR_ACCESSCONTROL_FILTER_TYPE => Config::ACCESSCONTROL_FILTER_USERAGENT,
                Config::ATTR_ACCESSCONTROL_FILTER_LIST => null
            ]
        ];

        try {
            app()->db($this->connection)->insert($data, $this->table);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
