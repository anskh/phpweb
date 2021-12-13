<?php

declare(strict_types=1);

namespace PhpWeb\Config;

use ArrayAccess;

class Config implements ArrayAccess
{
    public const ATTR_APP_CONFIG = 'application';
    public const ATTR_DB_CONFIG = 'database';
    public const ATTR_ACCESSCONTROL_CONFIG = 'access_control';
    public const ATTR_EXCEPTION_CONFIG = 'exception';

    protected array $container = [];
    protected string $path;
    protected string $environment;

    public function __construct(string $path, ?string $environment = null)
    {
        $this->path = $path;
        $this->environment = $environment;
    }

    public function get(string $key, $defaultValue = null)
    {
        return $this->offsetGet($key) ?? $defaultValue;
    }

    public function set(string $key, $value): void
    {

        if(is_array($key)){
            foreach($key as $innerKey => $innerValue){
                $this->arraySet($this->container, $innerKey, $innerValue);
            }
        }else{
            $this->arraySet($this->container, $key, $value);
        }

    }

    protected function arraySet(array &$array, string $key, $value): array
    {
        if(empty($key)){
            return $array = $value;
        }

        $keys = explode(".", $key);
        while(count($keys) > 1){
            $key = array_shift($keys);
            if(!isset($array[$key]) || !is_array($array[$key])){
                $array[$key] = [];
            }

            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;

        return $array;
    }

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
                if(!empty($this->environment)){
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

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->container[$offset] : null;

    }

    public function offsetSet($offset, $value): void
    {
        if(is_null($offset)){
            $this->container[] = $value;
        }else{
            $this->container[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        $this->container[$offset] = null;
    }
}