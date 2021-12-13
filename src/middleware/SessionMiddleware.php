<?php

declare(strict_types=1);

namespace PhpWeb\Middleware;

use PhpWeb\Http\Session\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class SessionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookies = $request->getCookieParams();
        $id = $cookies[session_name()] ?? Session::generateToken();
        $this->startSession($id);

        $session = new Session($id);
        
        $request = $request->withAttribute(Session::class, $session);
        $response = $handler->handle($request);

        $sid = $session->getId();
        if($id !== $sid){
            $this->startSession($sid);
        }

        $session->save();

        if($session->has() === false){
            return $response;
        }

        return $response->withHeader('Set-Cookie', sprintf('%s=%s; path=%s', session_name(), $sid, ini_get('session.cookie_path')));
    }

    private function startSession(string $id): void
    {
        session_id($id);
        session_start(['use_cookies'=>false, 'use_only_cookies'=>true]);
    }
}