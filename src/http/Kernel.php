<?php

declare(strict_types=1);

namespace PhpWeb\Http;

use PDO;
use PhpWeb\Config\Config;
use PhpWeb\Config\Environment;
use PhpWeb\Db\Database;
use PhpWeb\Db\MigrationBuilder;
use PhpWeb\Http\Auth\UserIdentity;
use PhpWeb\Http\Auth\UserIdentityInterface;
use PhpWeb\Http\Session\Session;
use PhpWeb\Http\Session\SessionInterface;
use PhpWeb\Model\DbModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WoohooLabs\Harmony\Harmony;

class Kernel
{
    public string $environment;

    private static Kernel $instance;
    private string $connection;
    private Harmony $app;
    private Config $config;

    /**
     * 
     */
    private final function __construct()
    {       
    }

    public static function getInstance(): self
    {
        if(!isset(self::$instance)){
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 
     */
    public static function init(string $path, ?string $environment = null): void
    {
        $environment = $environment ?? Environment::DEVELOPMENT;
        self::getInstance()->environment = $environment;
        self::getInstance()->config = new Config($path, $environment);
        self::getInstance()->connection = self::getInstance()->config->get(Config::ATTR_DB_CONFIG . '.' . Config::ATTR_DB_DEFAULT_CONNECTION);
    }

    /**
     * 
     */
    public static function handle(ServerRequestInterface $request, ResponseInterface $response) : Harmony
    {
        self::getInstance()->app =  new Harmony($request, $response);

        return self::getInstance()->app;
    }

    /**
     * 
     */
    public function config(?string $key = null, $defaultValue = null)
    {
        if($key){
            return $this->config->get($key, $defaultValue);
        }else{
            return $this->config;
        }
    }

    /**
     * 
     */
    public function user(): UserIdentityInterface
    {
        return $this->app->getRequest()->getAttribute(UserIdentity::class);
    }

    /**
     * 
     */
    public function session(): SessionInterface
    {
        return $this->app->getRequest()->getAttribute(Session::class);
    }

    /**
     * 
     */
    public function request(): ServerRequestInterface
    {
        return $this->app->getRequest();
    }

    /**
     * 
     */
    public function db(?string $connection = null): Database
    {
        $connection = $connection ?? $this->connection;
        return Database::connect($connection);
    }

    /**
     * 
     */
    public function dbModel(string $class): ?DbModel
    {
        $obj = new $class($this->connection);

        if($obj instanceof DbModel){
            return $obj;
        }

        return null;
    }

    /**
     * 
     */
    public function buildMigration(?string $connection = null, ?string $path = null, ?string $action = null): MigrationBuilder
    {
        $connection = $connection ?? $this->connection;
        return new MigrationBuilder($connection, $path, $action);
    }
}