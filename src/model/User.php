<?php

declare(strict_types=1);

namespace PhpWeb\Model;

class User extends DbModel
{
    protected string $table = 'user';
    protected bool $autoIncrement = true;
    protected string $primaryKey = 'id';
    protected array $fields = [
        'name','password','token','roles'
    ];

    public int $id;
    public string $name;
    public string $password;
    public string $token;
    public string $roles;

    public static function table(): string
    {
        return 'user';
    }
}