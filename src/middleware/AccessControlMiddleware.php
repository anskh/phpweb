<?php

declare(strict_types=1);

namespace PhpWeb\Middleware;

use PhpWeb\Http\Auth\AccessControl;
use PhpWeb\Http\Auth\UserIdentity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function PhpWeb\app;

final class AccessControlMiddleware implements MiddlewareInterface
{
    public const ATTR = 'access_control';
    public const ATTR_FILTER = 'filter';
    public const ATTR_PERMISSION = 'permission';
    public const ATTR_MODEL = 'model';


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $control = new AccessControl();

        $config = app()->config(self::ATTR, []);

        // by pass 
        if(empty($config)){
            return $handler->handle($request);
        }

        // return unauthorized
        if(!$control->filter($config[self::ATTR_FILTER] ?? [])){
            return $control->unauthorized();
        }

        $route = current_route();
        
        if(!$route){
            return $handler->handle($request);
        }

        // by pass request if not needed
        if(!$control->isAuthenticationRequired($route, $config[self::ATTR_PERMISSION] ?? [])){
            return $handler->handle($request);
        }

        $user = $control->authenticate($config[self::ATTR_MODEL] ?? '');
        $request = $request->withAttribute(UserIdentity::class, $user);
        
        // return forbidden is authentication fail
        if(!$user->isAuthenticated()){
            return $control->forbidden();
        }

        // return forbidden if not authorized
        if(!$control->authorize($user->getRoles())){
            return $control->forbidden();
        }

        return $handler->handle($request);
    }

}