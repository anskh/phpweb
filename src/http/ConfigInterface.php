<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http;

use ArrayAccess;
use Psr\Container\ContainerInterface;

/**
* Config interface
*
* @package    Anskh\PhpWeb\Http
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
interface ConfigInterface extends ArrayAccess, ContainerInterface
{
    /**
    * Set config value for certain id
    *
    * @param  string|array $id    Key of config, support dot key notation like app.defult_database
    * @param  mixed        $value value of config to set for certain id
    * @return void description
    */
    public function set($id, $value): void;

    /**
    * get environment
    *
    * @return string environment value, 'production' or 'development'
    */
    public function getEnvironment(): string;

    /**
    * get config path
    *
    * @return string config folder path  value, 'production' or 'development'
    */
    public function getPath(): string;
}