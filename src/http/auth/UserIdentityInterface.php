<?php

declare(strict_types=1);

namespace PhpWeb\Http\Auth;

interface UserIdentityInterface
{
    public function AuthenticationType(): string;

    public function getId();

    public function getName(): string;

    public function getRoles(): array;

    public function isAuthenticated(): bool;

    public function hasRole(string $role): bool;
} 