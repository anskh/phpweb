<?php

declare(strict_types=1);

namespace PhpWeb\Http\Auth;

interface AccessControlInterface
{
    public function filter(array $options): bool;

    public function isAuthenticationRequired(string $permission, array $options): bool;

    public function authenticate(string $modelClass): UserIdentityInterface;

    public function authorize(array $roles): bool;
}