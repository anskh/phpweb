<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\simpleDispatcher;

/**
 * Router class
 *
 * @package    Anskh\PhpWeb\Http
 * @author     Khaerul Anas <anasikova@gmail.com>
 * @copyright  2021-2022 Anskh Labs.
 * @version    1.0.0
 */
class Router implements RouterInterface
{
    protected array $routes = [];
    protected array $routePaths = [];
    protected array $routeParams = [];
    protected Dispatcher $dispatcher;

    /**
     * Constructor
     *
     * @param  array $routes Route definition
     * @return void 
     */
    public function __construct(array $routes)
    {
        $basePath = my_app()->getAttribute(App::ATTR_BASEPATH);
        foreach ($routes as $name => $route) {
            $routePath = $route[1];
            if ($pos = strpos($routePath, '[', 1)) {
                $routePath = substr($routePath, 0, $pos);
            }
            if ($pos = strpos($routePath, '{', 1)) {
                $routePath = substr($routePath, 0, $pos);
            }
            $this->routes[$name] = [$route[0], $basePath . $route[1], $route[2]];
            $this->routePaths[$name] = $basePath . $routePath; 
            $param = '';
            if($route[1] !== $routePath){
                $param = str_replace(['[',']',':\d',':\w','+'],['','','','',''], substr($route[1], strlen($routePath)));
            }
            $this->routeParams[$name] = $param;
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
            if($this->routeParams[$name]){
                if(substr($path, 0, strlen($routePath)) === $routePath){
                    return $name;
                }
            }else{
                if($path === $routePath){
                    return $name;
                }
            }
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function routeUrl(string $name, array $params = []): string
    {
        $routePath = $this->routePaths[$name];
        $routeParam = $this->routeParams[$name];
        if ($params && $routeParam) { 
            foreach ($params as $key => $value) {
                $routeParam = str_replace('{'.$key.'}', $value, $routeParam);
            }
            if($pos = strpos($routeParam,'{')){
                $routeParam = substr($routeParam, 0, $pos);
            }
        }

        return $routePath . $routeParam;
    }
}