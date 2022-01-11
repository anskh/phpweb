<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http;

use ArrayAccess;
use Psr\Container\ContainerInterface;

/**
* Config helper
*
* @package    Anskh\PhpWeb\Http
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
class Config implements ConfigInterface
{
    public const ATTR_EXCEPTION_UNAUTHORIZED = 'unauthorized';
    public const ATTR_EXCEPTION_FORBIDDEN = 'forbidden';
    public const ATTR_EXCEPTION_NOTFOUND = 'notfound';
    public const ATTR_EXCEPTION_THROWABLE = 'throwable';    
    public const ATTR_EXCEPTION_LOG = 'log';
    public const ATTR_EXCEPTION_LOG_NAME = 'name';
    public const ATTR_EXCEPTION_LOG_FILE = 'file';

    protected array $container = [];
    protected string $path;
    protected string $environment;

    /**
    * Constructor
    *
    * @param  string $path        Path of folder contains php file config
    * @param  string $environment Environment type
    * @return void
    */
    public function __construct(string $path, string $environment = Environment::DEVELOPMENT)
    {
        $this->path = $path;
        $this->environment = $environment;
    }

    /**
     * @inheritdoc
     */
    public function get(string $id)
    {
        return $this->offsetGet($id) ?? null;
    }

    /**
     * @inheritdoc
     */
    public function has(string $id): bool
    {
        return $this->offsetExists($id);
    }

    /**
    * Set config value for certain id
    *
    * @param  string|array $id    Key of config, support dot key notation like app.defult_database
    * @param  mixed        $value value of config to set for certain id
    * @return void description
    */
    public function set($id, $value): void
    {

        if(is_array($id)){
            foreach($id as $innerid => $innerValue){
                $this->arraySet($this->container, $innerid, $innerValue);
            }
        }else{
            $this->arraySet($this->container, $id, $value);
        }

    }

    /**
     * @inheritdoc
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @inheritdoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    protected function arraySet(array &$array, string $id, $value): array
    {
        if(empty($id)){
            return $array = $value;
        }

        $ids = explode(".", $id);
        while(count($ids) > 1){
            $id = array_shift($ids);
            if(!isset($array[$id]) || !is_array($array[$id])){
                $array[$id] = [];
            }

            $array = &$array[$id];
        }
        $array[array_shift($ids)] = $value;

        return $array;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        if(isset($this->container[$offset])){
            return true;
        }

        $name = strtok($offset, '.');
        if(isset($this->container[$name])){
            $p = $this->container[$name];
            while(false !== ($name = strtok('.'))){
                if(!isset($p[$name])){
                    return false;
                }

                $p = $p[$name];
            }
            $this->container[$offset] = $p;

            return true;
        }else{
            $file = "{$this->path}/{$name}.php";
            if(is_file($file) && is_readable($file)){
                $this->container[$name] = include $file;
                if($this->environment){
                    $file = "{$this->path}/{$this->environment}/{$name}.php";
                    if(is_file($file) && is_readable($file)){
                        $this->container[$name] = array_replace_recursive($this->container[$name], include $file);
                    }
                }

                return $this->offsetExists($offset);
            }

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->container[$offset] : null;

    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        if(is_null($offset)){
            $this->container[] = $value;
        }else{
            $this->container[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        $this->container[$offset] = null;
    }
}