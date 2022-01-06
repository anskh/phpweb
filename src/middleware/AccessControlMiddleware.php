<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Middleware;

use Anskh\PhpWeb\Config\Config;
use Anskh\PhpWeb\Http\Auth\AccessControl;
use Anskh\PhpWeb\Http\Auth\UserIdentity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Anskh\PhpWeb\app;
use function Anskh\PhpWeb\current_route;

final class AccessControlMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $control = new AccessControl(app()->config(Config::ATTR_APP_CONFIG . '.' . Config::ATTR_APP_ACCESSCONTROL));

        $user = $control->authenticate();
        $request = $request->withAttribute(UserIdentity::class, $user);

        // return unauthorized
        if(!$control->filter()){
            return $control->unauthorized();
        }


        $permission = current_route();
        
        // by pass request if not needed
        if(!$control->isAuthenticationRequired($permission)){
            return $handler->handle($request);
        }
        
        // return forbidden is authentication fail
        if(!$user->isAuthenticated()){
            return $control->forbidden();
        }

        // return forbidden if not authorized
        if(!$control->authorize($user->getRoles(), $permission)){
            return $control->forbidden();
        }

        return $handler->handle($request);
    }

}