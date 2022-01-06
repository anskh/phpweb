<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http;

use Anskh\PhpWeb\Config\Config;
use Anskh\PhpWeb\Config\Environment;
use Anskh\PhpWeb\Db\Database;
use Anskh\PhpWeb\Db\MigrationBuilder;
use Anskh\PhpWeb\Http\Auth\UserIdentity;
use Anskh\PhpWeb\Http\Auth\UserIdentityInterface;
use Anskh\PhpWeb\Http\Session\Session;
use Anskh\PhpWeb\Http\Session\SessionInterface;
use Anskh\PhpWeb\Model\DbModel;
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
            self::$instance = new static();
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