<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function PhpWeb\view;

return [
    'home' => ['GET', '/', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $response->getBody()->write('<html><body><h1>Hello world.</h1></body></html>');

        return $response;
    }],
    'hello' => ['GET', '/hello', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        
        return view('hello', $response);
    }]
];
