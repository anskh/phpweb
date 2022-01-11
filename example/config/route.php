<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
* Route configuration
*
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/

return [
    'home' => ['GET', '/', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $session = my_app()->session();
        if(!$session->has('counter')){
            $session->set('counter', 0);
        }
        $session->set('counter', $session->get('counter') + 1);

        $response->getBody()->write('<html><body><h1>Hello world ke-' . $session->get('counter') . '.</h1><p>Write from text hello world.</p></body></html>');

        return $response;
    }],
    'hello' => ['GET', '/hello', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        
        return my_view('hello', $response);
    }]
];
