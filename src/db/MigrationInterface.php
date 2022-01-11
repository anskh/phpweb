<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Db;

/**
* Migration Interface
*
* @package    Anskh\PhpWeb\Db
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
interface MigrationInterface
{
    /**
    * Up action, create or update db structure
    *
    * @return bool true if success, false otherwise
    */
    public function up() : bool;

    /**
    * Seed data to table
    *
    * @return bool true if success, false otherwise 
    */
    public function seed() : bool;

    /**
    * Drop structure
    *
    * @return bool true if success, false otherwise
    */
    public function down() : bool;
}