<?php

declare(strict_types=1);

use Anskh\PhpWeb\Http\Auth\AccessControl;

/**
* AccessControl configuration
*
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/

return [
    // list of route name which needs auth
    AccessControl::ATTR_PERMISSION => [
        
    ],
    // list of available role
    AccessControl::ATTR_ROLE => [
        'admin',
        'user'
    ],
    // mapping role with permission
    // role => [permission1, permission2]
    AccessControl::ATTR_ASSIGNMENT => [
        'admin'=>[],
        'user'=>[]
    ],
    // list of filter by specific attribute
    // deny if in list
    AccessControl::ATTR_FILTER => [
        AccessControl::FILTER_IP => [],
        AccessControl::FILTER_USER_AGENT => []
    ]
];