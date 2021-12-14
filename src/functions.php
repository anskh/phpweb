<?php

declare(strict_types=1);

namespace PhpWeb;

use InvalidArgumentException;
use Laminas\Diactoros\Response;
use PhpWeb\Config\Config;
use PhpWeb\Http\Kernel;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\PhpRenderer;

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

        $routes = app()->config(Config::ATTR_ROUTE_CONFIG, []);
        $base_path = app()->config(Config::ATTR_APP_CONFIG . '.' . Config::ATTR_APP_BASEPATH , '');

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
        $route = app()->config(Config::ATTR_ROUTE_CONFIG . ".$name", []);
        $base_path = app()->config(Config::ATTR_APP_CONFIG . '.' . Config::ATTR_APP_BASEPATH , '');

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

/**
 * 
 */
if (!function_exists("PhpWeb\view")) {
    function view(string $view, ?ResponseInterface $response = null, string $layout = '', array $data = [], int $status = 200): ResponseInterface
    {
        $response = $response ?? new Response();
        $config = app()->config(Config::ATTR_APP_CONFIG . '.' . Config::ATTR_APP_VIEW);
        $view .= $config[Config::ATTR_VIEW_FILE_EXT];
        if (!empty($layout)) {
            $layout = 'layout/' . $layout . $config[Config::ATTR_VIEW_FILE_EXT];
        }
        $renderer = new PhpRenderer($config[Config::ATTR_VIEW_PATH], $data, $layout);
        $renderer->render($response, $view);

        return $response->withStatus($status);
    }
}

/**
 * 
 */
if (!function_exists("PhpWeb\base_url")) {
    function base_url(string $url): string
    {
        return app()->config(Config::ATTR_APP_CONFIG . '.' . Config::ATTR_APP_BASEURL) . '/' . $url;
    }
}

/**
 * 
 */
if (!function_exists('PhpWeb\attributes_to_string')) {
    function attributes_to_string($attributes): string
    {
        if (empty($attributes)) {
            return '';
        }
        if (is_object($attributes)) {
            $attributes = (array) $attributes;
        }
        if (is_array($attributes)) {
            $atts = '';
            foreach ($attributes as $key => $val) {

                if (is_object($val)) {
                    $val = (array) $val;
                }
                if (is_array($val)) {
                    $val = "{" . attributes_to_string($val) . "}";
                }
                if (is_numeric($key)) {
                    $key = '';
                } else {
                    $key .= '=';
                    if (is_string($val)) {
                        $val = "\"{$val}\"";
                    }
                }
                $atts = empty($atts) ? ' ' . $key . $val : $atts . ' ' . $key  . $val;
            }
            return $atts;
        }
        if (is_string($attributes)) {
            return ' ' . $attributes;
        }

        return '';
    }
}