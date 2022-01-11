<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http\Auth;

use Psr\Http\Message\ResponseInterface;

/**
* Access control interface
*
* @package    Anskh\PhpWeb\Http\Auth
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
interface AccessControlInterface
{
    /**
    * Filter access based on ip and useragent
    *
    * @return bool true if all filter succesfully pass, otherwise false
    * @throws \InvalidArgumentException throw exception if filter access not valid
    */
    public function filter(): bool;

    /**
    * Check if $route need user isauthenticated to access
    *
    * @param  string $route route name
    * @return bool true if user need isauthenticated, false if is not
    */
    public function isAuthenticationRequired(string $route): bool;

    /**
    * Authenticate user based on session
    *
    * @return UserIdentityInterface user identity
    */
    public function authenticate(): UserIdentityInterface;

    /**
    * Authorize $user to access $route
    *
    * @param  UserIdentityInterface $user user identity
    * @param  string                $route route name
    * @return bool true if user is authorized, false otherwise
    */
    public function authorize(UserIdentityInterface $user, string $route): bool;

    /**
    * Unauthorized or unauthenticated response
    *
    * @param  int $status Http status code, default 401
    * @return ResponseInterface
    */
    public function unauthorized(int $status = 401): ResponseInterface;

    /**
    * Forbidden or access not allowed for certain role
    *
    * @param  int $status Http status code, default 403
    * @return ResponseInterface
    */
    public function forbidden(int $status = 403): ResponseInterface;
}