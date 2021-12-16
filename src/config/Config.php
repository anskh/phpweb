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
    public const ATTR_ROUTE_CONFIG = 'route';

    public const ATTR_APP_NAME = 'name';
    public const ATTR_APP_VERSION = 'version';
    public const ATTR_APP_VENDOR = 'vendor';
    public const ATTR_APP_VIEW = 'view';
    public const ATTR_VIEW_PATH = 'path';
    public const ATTR_VIEW_FILE_EXT = 'extension';
    public const ATTR_APP_BASEURL = 'base_url';
    public const ATTR_APP_BASEPATH = 'base_path';
    public const ATTR_APP_ENVIRONMENT = 'environment';
    public const ATTR_APP_ACCESSCONTROL = 'access_control';
    public const ATTR_ACCESSCONTROL_DRIVER = 'driver';
    public const ACCESSCONTROL_DRIVER_FILE = 'file';
    public const ACCESSCONTROL_DRIVER_DB = 'db';
    public const ATTR_ACCESSCONTROL_ROLE = 'roles';
    public const ATTR_ACCESSCONTROL_ROLE_NAME = 'name';
    public const ATTR_ACCESSCONTROL_PERMISSION = 'permissions';
    public const ATTR_ACCESSCONTROL_PERMISSION_NAME = 'name';
    public const ATTR_ACCESSCONTROL_FILTER = 'filters';
    public const ATTR_ACCESSCONTROL_FILTER_TYPE = 'type';
    public const ACCESSCONTROL_FILTER_IP = 'ip';
    public const ACCESSCONTROL_FILTER_USERAGENT = 'user_agent';
    public const ATTR_ACCESSCONTROL_FILTER_LIST = 'list';
    public const ATTR_ACCESSCONTROL_ASSIGNMENT = 'assignments';
    public const ATTR_ACCESSCONTROL_USERMODEL = 'user_model';

    public const ATTR_DB_DEFAULT_CONNECTION = 'default_connection';
    public const ATTR_DB_CONNECTION = 'connections';
    public const ATTR_DB_CONNECTION_DSN = 'dsn';
    public const ATTR_DB_CONNECTION_USER = 'user';
    public const ATTR_DB_CONNECTION_PASSWD = 'password';
    public const ATTR_DB_CONNECTION_TYPE = 'type';
    public const ATTR_DB_CONNECTION_SCHEMA = 'schema';
    public const ATTR_DB_PREFIX = 'prefix';
    public const ATTR_DB_MIGRATION = 'migration';
    public const ATTR_DB_MIGRATION_PATH = 'path';
    public const ATTR_DB_MIGRATION_ACTION = 'action';

    public const ATTR_EXCEPTION_UNAUTHORIZED = 'unauthorized';
    public const ATTR_EXCEPTION_FORBIDDEN = 'forbidden';
    public const ATTR_EXCEPTION_NOTFOUND = 'notfound';
    public const ATTR_EXCEPTION_THROWABLE = 'throwable';    
    public const ATTR_EXCEPTION_LOG = 'log';
    public const ATTR_EXCEPTION_LOG_NAME = 'name';
    public const ATTR_EXCEPTION_LOG_FILE = 'file';

    public const HASHING_ALGORITHM = PASSWORD_BCRYPT;
    public const ACCESSCONTROL_SEPARATOR = '|';


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