<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http;

use Exception;

/**
* Kernel class
*
* @package    Anskh\PhpWeb\Http
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
class Kernel
{
    protected static App    $app;
    protected static Config $config;

    /**
    * Init app and config
    *
    * @param  string $rootDir      Directory of app
    * @param  string $configDir    Directory of config file
    * @param  string $AppConfig    app config file
    * @param  string $environment  Environment type, 'production' or 'development'
    * @return void description
    */
    public static function init(string $rootDir, string $configDir, string $appConfig = 'app', string $environment = Environment::DEVELOPMENT): void
    {
        self::$config = new Config($configDir, $environment);
        self::$app = new App($rootDir, self::$config->get($appConfig));
    }

    /**
    * Get current app
    *
    * @throws \Exception Throw exception if Anskh\PhpWeb\Http\App is not init before
    * @return App Current app
    */
    public static function app(): App
    {
        if(!isset(self::$app)){
            throw new Exception('Please call Kerner::init() first.');
        }

        return self::$app;
    }

    /**
    * Get current config object
    *
    * @throws \Exception Throw exception if Anskh\PhpWeb\Http\Config is not init before
    * @return ConfigInterface config 
    */
    public static function config(): ConfigInterface
    {
        if(!isset(self::$config)){
            throw new Exception('Please call Kerner::init() first.');
        }

        return self::$config;
    }
}