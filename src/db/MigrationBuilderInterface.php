<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Db;

/**
* MigrationBuilderInterface
*
* @package    Anskh\PhpWeb\Db
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/

interface MigrationBuilderInterface
{
    /**
    * Apply migration
    *
    * @return void
    */
    public function applyMigration(): void;    
}