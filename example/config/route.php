<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

return [
    'home' => ['GET', '/', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $response->getBody()->write('<html><body><h1>Hello world.</h1><p>Write from text hello world.</p></body></html>');

        return $response;
    }],
    'hello' => ['GET', '/hello', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        
        return my_view('hello', $response);
    }]
];
