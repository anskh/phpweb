<?php

declare(strict_types=1);

use PhpWeb\Config\Config;
use PhpWeb\Config\Environment;
use PhpWeb\Model\User;

return [
    Config::ATTR_APP_NAME => 'Example',
    Config::ATTR_APP_VERSION => '1.0',
    Config::ATTR_APP_VENDOR => 'Khaerul Anas',
    Config::ATTR_APP_VIEW => [
        Config::ATTR_VIEW_PATH => ROOT . '/view',
        Config::ATTR_VIEW_FILE_EXT => '.phtml'
    ],
    Config::ATTR_APP_BASEURL => 'http://localhost/phpweb/example/public',
    Config::ATTR_APP_BASEPATH => '/phpweb/example/public',
    Config::ATTR_APP_ENVIRONMENT => Environment::DEVELOPMENT,
    Config::ATTR_APP_ACCESSCONTROL => [
        Config::ATTR_ACCESSCONTROL_DRIVER => Config::ACCESSCONTROL_DRIVER_FILE,
        Config::ACCESSCONTROL_DRIVER_FILE => Config::ATTR_ACCESSCONTROL_CONFIG,
        Config::ACCESSCONTROL_DRIVER_DB => 'mysql',
        Config::ATTR_ACCESSCONTROL_USERMODEL => User::class
    ]
];
