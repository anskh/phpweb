<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http\Auth;

/**
* User identity class
*
* @package    Anskh\PhpWeb\Http\Auth
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
class UserIdentity implements UserIdentityInterface
{
    protected $id;
    protected string $name;
    protected array $roles;

    /**
    * Constructor
    *
    * @param  string|int $id   User id, default empty
    * @param  string     $name User name, default empty
    * @param  array      $roles User roles, default empty array
    * @return void
    */
    public function __construct($id = '', string $name = '', array $roles = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->roles = $roles;
    }

    /**
     * @inheritdoc
     */
    public function AuthenticationType(): string
    {
        return 'User Authentication';
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @inheritdoc
     */
    public function isAuthenticated(): bool
    {
        return empty($this->name) ? false: true;
    }

    /**
     * @inheritdoc
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }
}