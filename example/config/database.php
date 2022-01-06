<?php

declare(strict_types=1);

use Anskh\PhpWeb\Config\Config;
use Anskh\PhpWeb\Db\Database;

return [
    Config::ATTR_DB_DEFAULT_CONNECTION => 'mysql',
    Config::ATTR_DB_CONNECTION => [
        'mysql' => [
            Config::ATTR_DB_CONNECTION_DSN => Database::MYSQL . ':host=localhost;port=3306;dbname=test',
            Config::ATTR_DB_CONNECTION_USER => 'root',
            Config::ATTR_DB_CONNECTION_PASSWD => 'password'
        ],
        'sqlite' => [
            Config::ATTR_DB_CONNECTION_DSN => Database::SQLITE . ':' . ROOT . '/writeable/db/test.db'
        ],
        'pgsql' => [
            Config::ATTR_DB_CONNECTION_DSN => Database::PGSQL . ':host=localhost;port=5432;dbname=test',
            Config::ATTR_DB_CONNECTION_USER => 'postgres',
            Config::ATTR_DB_CONNECTION_PASSWD => 'password',
            Config::ATTR_DB_CONNECTION_SCHEMA => 'public'
        ],
        'sqlsrv' => [
            Config::ATTR_DB_CONNECTION_DSN => Database::SQLSRV .':Server=.\\sqlexpress;Database=test',
            Config::ATTR_DB_CONNECTION_USER => 'sa',
            Config::ATTR_DB_CONNECTION_PASSWD => 'password',
            Config::ATTR_DB_CONNECTION_SCHEMA => 'dbo'
        ]
    ],
    Config::ATTR_DB_PREFIX => 'tbl_',
    Config::ATTR_DB_MIGRATION => [
        Config::ATTR_DB_MIGRATION_PATH => ROOT . '/migration',
        Config::ATTR_DB_MIGRATION_ACTION => 'up'
    ]
];
