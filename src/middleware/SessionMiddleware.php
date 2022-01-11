<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Middleware;

use Anskh\PhpWeb\Http\Session\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
* SessionMiddleware
*
* @package    Anskh\PhpWeb\Middleware
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
final class SessionMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
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

    /**
    * Start Session
    *
    * @param  string $id session id
    * @return void
    */
    private function startSession(string $id): void
    {
        session_id($id);
        session_start(['use_cookies'=>false, 'use_only_cookies'=>true]);
    }
}