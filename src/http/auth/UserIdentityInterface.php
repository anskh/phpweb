<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http\Auth;

interface UserIdentityInterface
{
    /**
    * Auth type
    *
    * @return string default is 'User Authentication'
    */
    public function AuthenticationType(): string;

    /**
    * Get user id
    *
    * @return string user id
    */
    public function getId();

    /**
    * Get user name
    *
    * @return string user name
    */
    public function getName(): string;

    /**
    * Get user roles
    *
    * @return array user roles
    */
    public function getRoles(): array;

    /**
    * Check if use is athenticated
    *
    * @return bool true if user is authenticated, false otherwise
    */
    public function isAuthenticated(): bool;

    /**
    * Check if user has specific role
    *
    * @param  string $role Role name
    * @return bool true if has, false otherwise
    */
    public function hasRole(string $role): bool;
} 