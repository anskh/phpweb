<?php

declare(strict_types=1);

namespace PhpWeb\Http;

use PDO;
use PhpWeb\Config\Config;
use PhpWeb\Config\Environment;
use PhpWeb\Db\Database;
use PhpWeb\Http\Auth\UserIdentityInterface;
use PhpWeb\Http\Session\SessionInterface;
use PhpWeb\Model\DbModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WoohooLabs\Harmony\Harmony;

class Kernel
{
    public string $environment;

    private static ?Kernel $instance = null;
    private Harmony $app;
    private Config $config;

    /**
     * 
     */
    private final function __construct()
    {        
    }

    /**
     * 
     */
    public static function init(string $path, ?string $environment = null): void
    {
        $environment = $environment ?? Environment::DEVELOPMENT;
        self::getInstance()->environment = $environment;
        self::getInstance()->config = new Config($path, $environment);
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
    public static function getInstance(): self
    {
        if(!self::$instance){
            self::$instance = new Kernel();
        }

        return self::$instance;
    }

    /**
     * 
     */
    public function config(?string $key = null, $defaultValue = null)
    {
        if($key){
            return self::getInstance()->config->get($key, $defaultValue);
        }else{
            return self::getInstance()->config;
        }
    }

    /**
     * 
     */
    public function user(): UserIdentityInterface
    {
        return self::getInstance()->app->request->getAttribute('');
    }

    /**
     * 
     */
    public function session(): SessionInterface
    {
        return self::getInstance()->app->request->getAttribute('');
    }

    /**
     * 
     */
    public function request(): ServerRequestInterface
    {
        return self::getInstance()->app->request;
    }

    /**
     * 
     */
    public function db(?string $connection = null): Database
    {
        return Database::connect($connection);
    }

    /**
     * 
     */
    public function dbModel(string $class): ?DbModel
    {
        $obj = new $class();

        if($obj instanceof DbModel){
            return $obj;
        }

        return null;
    }
}