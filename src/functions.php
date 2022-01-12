<?php

declare(strict_types=1);

use Anskh\PhpWeb\Http\App;
use Anskh\PhpWeb\Http\Config;
use Anskh\PhpWeb\Http\Kernel;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\PhpRenderer;

/**
* List of core function
*
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/

if (!function_exists('my_app')) {
    /**
    * Get current app
    *
    * @return App current app
    */
    function my_app(): App
    {
        return Kernel::app();
    }
}

if (!function_exists('my_config')) {
    /**
    * Get current config hadler
    *
    * @return Anskh\PhpWeb\Http\Config current config handler
    */
    function my_config(): Config
    {
        return Kernel::config();
    }
}

if (!function_exists('my_client_ip')) {
    /**
    * Get client ip v4
    *
    * @return string ip v4, if fail return 'unknown'
    */
    function my_client_ip(): string
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
        if (($ip && ($ip == getenv('SERVER_ADDR')) && getenv('REMOTE_ADDR') || !filter_var($ip, FILTER_VALIDATE_IP))) {
            $ip = getenv('REMOTE_ADDR');
        }

        // if local ip
        if(!$ip){
            $ip = gethostbyname(gethostname());
        }

        if ($ip && filter_var($ip, FILTER_VALIDATE_IP)!== false) {
            return $ip;
        }  

        return 'unknown';
    }
}

if (!function_exists('my_user_agent')) {
    /**
    * Get client user agent
    *
    * @return string Client user agent, if fail return 'unknown'
    */
    function my_user_agent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'uknown';
    }
}

if(!function_exists('my_class_name')){
    /**
    * Get class name, without namespace
    *
    * @param  string|object $class full class name or object
    * @return string class name
    */
    function my_class_name($class): string
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

if (!function_exists('my_str_starts_with')) {
    /**
    * Check if $haystack has start with $needle
    *
    * @param  string $haystack String to check
    * @param  string $needle   String to search
    * @return bool true if $haystack start with $needle
    */
    function my_str_starts_with(string $haystack, string $needle): bool
    {
        $len = strlen($needle);

        if (strlen($haystack) < $len) {
            return false;
        }

        return (substr($haystack, 0, $len) === $needle);
    }
}

if (!function_exists('my_str_ends_with')) {
    /**
    * Check if $haystack has end with $needle
    *
    * @param  string $haystack String to check
    * @param  string $needle   String to search
    * @return bool true if $haystack end with $needle
    */
    function my_str_ends_with(string $haystack, string $needle): bool
    {
        $len = strlen($needle);

        if ($len == 0) {
            return true;
        }

        if (strlen($haystack) < $len) {
            return false;
        }

        return (substr($haystack, -$len) === $needle);
    }
}

if (!function_exists('my_current_route')) {
    /**
    * Get current route name
    *
    * @return string route name
    */
    function my_current_route(): string
    {
        static $current_route;
        if($current_route){
            return $current_route;
        }

        $path = my_app()->request()->getUri()->getPath();
        $current_route = my_app()->router()->getRouteName($path);

        return $current_route;
    }
}

/**
 * 
 */
if (!function_exists('my_route_to')) {
    function my_route_to(string $name, array $params = []): string
    {
        return my_app()->router()->routeUrl($name, $params);
    }
}

/**
 * 
 */
if (!function_exists("my_view")) {
    function my_view(string $view, ?ResponseInterface $response = null, string $layout = '', array $data = [], int $status = 200): ResponseInterface
    {
        $response = $response ?? new Response();
        $path = my_app()->getAttribute(App::ATTR_VIEW_PATH);
        $ext = my_app()->getAttribute(App::ATTR_VIEW_EXT);
        $view .= $ext;
        if ($layout) {
            $layout .= $ext;
        }
        $renderer = new PhpRenderer($path, $data, $layout);
        $response = $renderer->render($response, $view);

        return $response->withStatus($status);
    }
}

/**
 * 
 */
if (!function_exists("my_base_url")) {
    function my_base_url(string $url): string
    {
        return my_app()->getAttribute(App::ATTR_BASEURL) . '/' . $url;
    }
}

/**
 * 
 */
if (!function_exists('my_attributes_to_string')) {
    function my_attributes_to_string($attributes): string
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
                    $val = "{" . my_attributes_to_string($val) . "}";
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