<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\simpleDispatcher;

/**
 * Router class
 *
 * @package    Anskh\PhpWeb\Http\Router
 * @author     Khaerul Anas <anasikova@gmail.com>
 * @copyright  2021-2022 Anskh Labs.
 * @version    1.0.0
 */
class Router implements RouterInterface
{
    protected array $routes;
    protected array $routePaths;
    protected Dispatcher $dispatcher;

    /**
     * Constructor
     *
     * @param  array $routes Route definition
     * @return void 
     */
    public function __construct(array $routes)
    {
        $this->routes = [];
        $this->routePaths = [];
        $basePath = my_app()->getAttribute(App::ATTR_BASEPATH);
        foreach ($routes as $name => $route) {
            $routePath = $route[1];
            if (($pos = strpos('[', $routePath, 1)) !== false) {
                $routePath = substr($routePath, 0, $pos);
            }
            if (($pos = strpos('{', $routePath, 1)) !== false) {
                $routePath = substr($routePath, 0, $pos);
            }
            $this->routes[$name] = [$route[0], $basePath . $route[1], $route[2]];
            $this->routePaths[$name] = $basePath . $routePath; 
        }
    }

    /**
     * @inheritdoc
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @inheritdoc
     */
    public function getRoute(string $name): array
    {
        return $this->routes[$name] ?? [];
    }

    /**
     * @inheritdoc
     */
    public function getDispatcher(): Dispatcher
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = simpleDispatcher(function(RouteCollector $r){
                $routes = $this->routes;
                foreach($routes as $route){
                    list($method, $path, $handler) = $route;

                    $r->addRoute($method, $path, $handler);
                }
            });
        }

        return $this->dispatcher;
    }

    /**
     * @inheritdoc
     */
    public function getRouteName(string $path): string
    {
        foreach($this->routePaths as $name => $routePath){
            if(substr($path, 0, strlen($routePath)) === $routePath){
                return $name;
            }
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function routeUrl(string $name, array $params = []): string
    {
        $url = $this->routePaths[$name];
        if(!$params && $this->routes[$name])
        if ($params) {
            $url = str_replace([':\d', ':\w', '+'], ['', '', ''], $url);
            foreach ($params as $key => $value) {
                $url = str_replace("{{$key}}", $value, $url);
            }
        }

        return $url;
    }
}
