<?php

declare(strict_types=1);

use Anskh\PhpWeb\Config\Config;

return [
    // list of route name which needs auth
    Config::ATTR_ACCESSCONTROL_PERMISSION => [
        
    ],
    // list of available role
    Config::ATTR_ACCESSCONTROL_ROLE => [
        'admin',
        'user'
    ],
    // mapping role with permission
    // role => [permission1, permission2]
    Config::ATTR_ACCESSCONTROL_ASSIGNMENT => [
        'admin'=>[],
        'user'=>[]
    ],
    // list of filter by specific attribute
    // deny if in list
    Config::ATTR_ACCESSCONTROL_FILTER => [
        Config::ACCESSCONTROL_FILTER_IP => [],
        Config::ACCESSCONTROL_FILTER_USERAGENT => []
    ]
];