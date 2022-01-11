<?php

declare(strict_types=1);

use Anskh\PhpWeb\Db\Database;

/**
* Database configuration
*
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/

return [
    'local' => [
            Database::ATTR_DSN => Database::MYSQL . ':host=localhost;port=3306;dbname=resepsionis',
            Database::ATTR_USER => 'root',
            Database::ATTR_PASS => 'password',
            Database::ATTR_SCHEMA => '',
            Database::ATTR_PREFIX => 'tbl_'
        ],
    'sqlite' => [
        Database::ATTR_DSN => Database::SQLITE . ':' . ROOT . '/writeable/db/test.db',
        Database::ATTR_USER => '',
        Database::ATTR_PASS => '',
        Database::ATTR_SCHEMA => '',
        Database::ATTR_PREFIX => 'tbl_'
    ],
    'pgsql' => [
        Database::ATTR_DSN => Database::PGSQL . ':host=localhost;port=5432;dbname=test',
        Database::ATTR_USER => 'postgres',
        Database::ATTR_PASS => 'password',
        Database::ATTR_SCHEMA => 'public',
        Database::ATTR_PREFIX => 'tbl_'
    ],
    'sqlsrv' => [
        Database::ATTR_DSN => Database::SQLSRV .':Server=.\\sqlexpress;Database=test',
        Database::ATTR_USER => 'sa',
        Database::ATTR_PASS => 'password',
        Database::ATTR_SCHEMA => 'dbo',
        Database::ATTR_PREFIX => 'tbl_'
    ]
];
