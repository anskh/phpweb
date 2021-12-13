<?php

declare(strict_types=1);

namespace PhpWeb;

use InvalidArgumentException;
use PhpWeb\Http\Kernel;

/**
 * 
 */
if (!function_exists('PhpWeb\app')) {
    function app(): Kernel
    {
        return Kernel::getInstance();
    }
}

/**
 * 
 */
if (!function_exists('PhpWeb\client_ip')) {
    function client_ip(): string
    {
        if ($ip = getenv('HTTP_CLIENT_IP')) :
        elseif ($ip = getenv('HTTP_X_FORWARDED_FOR')) :
        elseif ($ip = getenv('HTTP_X_FORWARDED')) :
        elseif ($ip = getenv('HTTP_FORWARDED_FOR')) :
        elseif ($ip = getenv('HTTP_FORWARDED')) :
        else :
            $ip = getenv('REMOTE_ADDR');
        endif;

        //If HTTP_X_FORWARDED_FOR == server ip
        if ((($ip) && ($ip == getenv('SERVER_ADDR')) && (getenv('REMOTE_ADDR')) || (!filter_var($ip, FILTER_VALIDATE_IP)))) {
            $ip = getenv('REMOTE_ADDR');
        }

        if ($ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $ip = '';
            }
        } else {
            $ip = '';
        }

        return $ip;
    }
}

/**
 * 
 */
if (!function_exists('PhpWeb\user_agent')) {
    function user_agent(): string
    {
        return app()->request()->getServerParams()['HTTP_USER_AGENT'] ?? '';
    }
}

/**
 * 
 */
if(!function_exists('PhpWeb\class_name')){
    function class_name($class): string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        
        if (!is_string($class)) {
            throw new InvalidArgumentException('Argument must be string or object type.');
        }

        $arr = explode('\\', $class);

        return end($arr);
    }
}

/**
 * 
 */
if (!function_exists('PhpWeb\str_search')) {
    function str_search(string $search, string $string, int $startpos = 0): int
    {
        $position = strpos($string, $search, $startpos);
        if (is_numeric($position)) {
            return $position;
        }
        
        return -1;
    }
}

/**
 * 
 */
if (!function_exists('PhpWeb\str_starts_with')) {
    function str_starts_with(string $string, string $startString): bool
    {
        $len = strlen($startString);

        if (strlen($string) < $len) {
            return false;
        }

        return (substr($string, 0, $len) === $startString);
    }
}

if (!function_exists('PhpWeb\str_ends_with')) {
    function str_ends_with(string $string, string $endString): bool
    {
        $len = strlen($endString);

        if ($len == 0) {
            return true;
        }

        if (strlen($string) < $len) {
            return false;
        }

        return (substr($string, -$len) === $endString);
    }
}

/**
 * 
 */
if (!function_exists('PhpWeb\current_route')) {
    function current_route(): string
    {
        $path = app()->request()->getUri()->getPath();

        $routes = app()->config('route', []);
        $base_path = app()->config('application.base_path', '');

        foreach ($routes as $permission => $route) {

            $route_path = $base_path . $route[1];
            $search = ["{", "["];

            foreach ($search as $s) {
                $pos = str_search($s, $route_path, 1);
                if ($pos >= 0) {
                    $route_path = substr($route_path, 0, $pos);
                }
            }

            if ($route_path !== $base_path . $route[1]) {
                if (str_starts_with($path, $route_path)) {
                    return $permission;
                }
            } else {
                if ($path === $route_path) {
                    return $permission;
                }
            }
        }

        return '';
    }
}

/**
 * 
 */
if (!function_exists('PhpWeb\route_to')) {
    function route_to(string $name, array $params = []): string
    {
        $route = app()->config("route.$name", []);
        $base_path = app()->config('application.base_path', '');

        if (empty($route)) {
            throw new InvalidArgumentException("Route not found.");
        }

        $url = $route[1];

        if (str_search("{", $url, 1) >= 0 && !$params) {
            throw new InvalidArgumentException("route $name can't be empty params");
        }


        if ($params) {
            $search = [":\d", "+", ":\w"];
            $replace = "";
            $url = str_replace($search, $replace, $url);
            foreach ($params as $param => $value) {
                $url = str_replace("{{$param}}", $value, $url);
            }
        }

        return $base_path . $url;
    }
}
