<?php

declare(strict_types=1);

use Anskh\PhpWeb\Http\App;
use Anskh\PhpWeb\Model\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
* Application configuration
*
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/

return [
    App::ATTR_NAME => 'Example',
    App::ATTR_VERSION => '1.0',
    App::ATTR_VENDOR => 'Khaerul Anas',
    App::ATTR_VIEW_PATH => ROOT . '/view',
    App::ATTR_VIEW_EXT => '.phtml',
    App::ATTR_BASEURL => 'http://localhost/phpweb/example',
    App::ATTR_BASEPATH => '/phpweb/example',
    App::ATTR_DEFAULT_CONNECTION => 'local',
    App::ATTR_USER_MODEL => User::class,
    App::ATTR_FORBIDDEN => static function(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    },
    App::ATTR_UNAUTHORIZED => static function(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
];
