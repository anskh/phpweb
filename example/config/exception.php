<?php

declare(strict_types=1);

use Laminas\Diactoros\Response;
use Anskh\PhpWeb\Config\Config;
use Psr\Http\Message\ResponseInterface;

return [
    // callable when unauthorized
    Config::ATTR_EXCEPTION_UNAUTHORIZED => function(int $status = 401): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('Maaf, halaman tidak tersedia untuk Anda.');

        return $response->withStatus($status);
    },
    
    // callable when forbidden
    Config::ATTR_EXCEPTION_FORBIDDEN => function(int $status = 302): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('Maaf, halaman tidak dapat diakses untuk Anda.');

        return $response->withStatus($status);
    },

    // callable when notfound
    Config::ATTR_EXCEPTION_NOTFOUND => function(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('Maaf, halaman tidak ditemukan.');

        return $response->withStatus(404);
    },

    Config::ATTR_EXCEPTION_LOG => [
        Config::ATTR_EXCEPTION_LOG_NAME => 'app',
        Config::ATTR_EXCEPTION_LOG_FILE => ROOT . '/writeable/log/app.log'
    ]

    // callable when error
    //Config::ATTR_EXCEPTION_THROWABLE => static function(Throwable $exception){
    //    return view('error500', null, 'main', ['title'=>'Resepsionis BPS - Kesalahan 500', 'exception'=>$exception]);
    //},
];