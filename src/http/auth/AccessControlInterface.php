<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http\Auth;

interface AccessControlInterface
{
    public function filter(): bool;

    public function isAuthenticationRequired(string $permission): bool;

    public function authenticate(): UserIdentityInterface;

    public function authorize(array $roles, string $permission): bool;
}