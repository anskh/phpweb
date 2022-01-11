<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Middleware;

use Anskh\PhpWeb\Http\Auth\AccessControl;
use Anskh\PhpWeb\Http\Auth\UserIdentity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
* AccessControlMiddleware
*
* @package    Anskh\PhpWeb\Middleware
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
final class AccessControlMiddleware implements MiddlewareInterface
{
    private string $accessControlAttribute;
    private string $driver;

    /**
    * Contructor
    *
    * @param  string $accessControlAttribute access control attribute
    * @param  string $driver access control driver
    * @return void
    */
    public function __construct(string $accessControlAttribute = 'accesscontrol', string $driver = AccessControl::DRIVER_FILE)
    {
        $this->accessControlAttribute = $accessControlAttribute;
        $this->driver = $driver;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $control = new AccessControl($this->accessControlAttribute, $this->driver);

        $user = $control->authenticate();
        $request = $request->withAttribute(UserIdentity::class, $user);

        // return unauthorized
        if(!$control->filter()){
            return $control->unauthorized();
        }


        $permission = my_current_route();
        
        // by pass request if not needed
        if(!$control->isAuthenticationRequired($permission)){
            return $handler->handle($request);
        }
        
        // return forbidden is authentication fail
        if(!$user->isAuthenticated()){
            return $control->forbidden();
        }

        // return forbidden if not authorized
        if(!$control->authorize($user, $permission)){
            return $control->forbidden();
        }

        return $handler->handle($request);
    }
}