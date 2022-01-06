<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http\Auth;

class UserIdentity implements UserIdentityInterface
{
    protected $id;
    protected string $name;
    protected array $roles;

    public function __construct($id = '', string $name = '', array $roles = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->roles = $roles;
    }

    public function AuthenticationType(): string
    {
        return 'User Authentication';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function isAuthenticated(): bool
    {
        return empty($this->name) ? false: true;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }
}