<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Model;

/**
* User class
*
* @package    Anskh\PhpWeb\Model
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
class User extends DbModel
{
    public const ATTR_TABLE = 'user';
    public const ATTR_NAME = 'name';
    public const ATTR_PASSWORD = 'password';
    public const ATTR_TOKEN = 'token';
    public const ATTR_ROLES = 'roles';

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
        return self::ATTR_TABLE;
    }
}