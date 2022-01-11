<?php

declare(strict_types=1);

use Anskh\PhpWeb\Http\AppException;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;

/**
* Exception configuration
*
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/

return [
    // callable when unauthorized
    AppException::ATTR_NOTFOUND => static function(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('Maaf, halaman tidak ditemukan.');

        return $response->withStatus(404);
    },

    AppException::ATTR_LOG_NAME  => 'app',
    AppException::ATTR_LOG_FILE => ROOT . '/writeable/log/app.log',

    // callable when error
    AppException::ATTR_THROWABLE => null
];