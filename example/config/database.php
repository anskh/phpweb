<?php

declare(strict_types=1);

use PhpWeb\Config\Config;

return [
    Config::ATTR_DB_DEFAULT_CONNECTION => 'mysql',
    Config::ATTR_DB_CONNECTION => [
        'mysql' => [
            Config::ATTR_DB_CONNECTION_DSN => 'mysql:host=localhost;port=3306;dbname=resepsionis',
            Config::ATTR_DB_CONNECTION_USER => 'root',
            Config::ATTR_DB_CONNECTION_PASSWD => 'password'
        ],
        'sqlite' => [
            Config::ATTR_DB_CONNECTION_DSN => 'sqlite:' . ROOT . '/writeable/db/resepsionis.db'
        ],
        'pgsql' => [
            Config::ATTR_DB_CONNECTION_DSN => 'pgsql:host=localhost;port=5432;dbname=resepsionis',
            Config::ATTR_DB_CONNECTION_USER => 'postgres',
            Config::ATTR_DB_CONNECTION_PASSWD => 'password'
        ]
    ],
    Config::ATTR_DB_PREFIX => 'tbl_',
    Config::ATTR_DB_MIGRATION => [
        Config::ATTR_DB_MIGRATION_PATH => ROOT . '/migration',
        Config::ATTR_DB_MIGRATION_ACTION => 'up'
    ]
];
