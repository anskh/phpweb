<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http;

use Anskh\PhpWeb\Db\Database;
use Anskh\PhpWeb\Db\MigrationBuilder;
use Anskh\PhpWeb\Http\Auth\UserIdentity;
use Anskh\PhpWeb\Http\Auth\UserIdentityInterface;
use Anskh\PhpWeb\Http\Session\Session;
use Anskh\PhpWeb\Http\Session\SessionInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WoohooLabs\Harmony\Harmony;

/**
 * App
 *
 * @package    Anskh\PhpWeb\Http
 * @author     Khaerul Anas <anasikova@gmail.com>
 * @copyright  2021-2022 Anskh Labs.
 * @version    1.0.0
 */
class App implements AttributeInterface
{
    public const ATTR_NAME               = 'app_name';
    public const ATTR_VERSION            = 'app_version';
    public const ATTR_VENDOR             = 'app_vendor';
    public const ATTR_VIEW_PATH          = 'view_path';
    public const ATTR_VIEW_EXT           = 'view_extension';
    public const ATTR_BASEURL            = 'base_url';
    public const ATTR_BASEPATH           = 'base_path';
    public const ATTR_DEFAULT_CONNECTION = 'default_connection';
    public const ATTR_USER_MODEL         = 'user_model';
    public const ATTR_UNAUTHORIZED       = 'unauthorized';
    public const ATTR_FORBIDDEN          = 'forbidden';

    protected string  $rootDir;
    protected array   $config;
    protected Harmony $handler;
    protected RouterInterface $router;
    protected array   $attributes = [
        self::ATTR_NAME,
        self::ATTR_VERSION,
        self::ATTR_VENDOR,
        self::ATTR_VIEW_PATH,
        self::ATTR_VIEW_EXT,
        self::ATTR_BASEURL,
        self::ATTR_BASEPATH,
        self::ATTR_DEFAULT_CONNECTION,
        self::ATTR_USER_MODEL,
        self::ATTR_UNAUTHORIZED,
        self::ATTR_FORBIDDEN
    ];

    /**
    * description
    *
    * @param  string param description
    * @return void description
    */
    public function __construct(string $rootDir, array $config)
    {
        $this->rootDir = $rootDir;
        foreach($config as $id => $value){
            $this->setAttribute($id, $value);
        } 
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($id)
    {
        if(!in_array($id, $this->attributes)){
            throw new InvalidArgumentException("$id not valid.");
        }

        return $this->config[$id] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setAttribute($id, $value): void
    {
        if(!in_array($id, $this->attributes)){
            throw new InvalidArgumentException("$id not valid.");
        }

        $this->config[$id] = $value;
    }

    /**
    * Handle request that implements Psr\Http\Message\ServerRequestInterface
    *
    * @param  ServerRequestInterface $request request
    * @param  ResponseInterface      $response response
    * @param  string                 $routeAttribute route config
    * @return Harmony Request handler
    */
    public function handle(ServerRequestInterface $request, ResponseInterface $response, string $routeAttribute) : Harmony
    {
        $this->handler = new Harmony($request, $response);
        $this->router = new Router(my_config()->get($routeAttribute));

        return $this-> handler;
    }


    /**
    * Get current user
    *
    * @return UserIdentityInterface Current user
    */
    public function user(): UserIdentityInterface
    {
        return $this->handler->getRequest()->getAttribute(UserIdentity::class);
    }

    /**
    * Get current session
    *
    * @return SessionInterface Current session
    */
    public function session(): SessionInterface
    {
        return $this->handler->getRequest()->getAttribute(Session::class);
    }

    /**
    * Get current request
    *
    * @return ServerRequestInterface Current request
    */
    public function request(): ServerRequestInterface
    {
        return $this->handler->getRequest();
    }

    /**
    * Get current router
    *
    * @return RouterInterface Current router
    */
    public function router(): RouterInterface
    {
        return $this->router;
    }

    /**
    * Get database based on connection name
    *
    * @param  string $connection Connection name
    * @param  string $dbAttribute db configuration name
    * @return Database Database base on connection name
    */
    public function db(?string $connection = null, string $dbAttribute = 'db'): Database
    {
        $connection = $connection ?? $this->getAttribute(self::ATTR_DEFAULT_CONNECTION);

        return Database::connect($connection, $dbAttribute);
    }

    /**
    * Create migration builder
    *
    * @param  string $connection Connection name
    * @param  string $path       Directory of migration file
    * @param  string $action     migration action
    * @param  string $dbAttribute Db attribute configuration
    * @return MigrationBuilder
    */
    public function buildMigration(string $connection, string $path, string $action = 'up', string $dbAttribute = 'db'): MigrationBuilder
    {
        $connection = $connection ?? $this->getAttribute(self::ATTR_DEFAULT_CONNECTION);

        return new MigrationBuilder($connection, $path, $action);
    }
}
