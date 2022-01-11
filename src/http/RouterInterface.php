<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Http;

use FastRoute\Dispatcher;

/**
* Router interface
*
* @package    Anskh\PhpWeb\Http
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/

interface RouterInterface
{
    /**
    * get route definition
    *
    * @return array route definition
    */
    public function getRoutes(): array;

    /**
    * get route for specific name
    *
    * @param  string $name name of route
    * @return array route definition for $name, [method, path, handler]
    */
    public function getRoute(string $name): array;

    /**
    * Get fast route dispatcher
    *
    * @return \FastRoute\Dispatcher Fast route dispatcher
    */
    public function getDispatcher(): Dispatcher;

    /**
    * Get route name from given path
    *
    * @param  string $path Given path
    * @return string the name of route
    */
    public function getRouteName(string $path): string;

    /**
    * Get url from given route name
    *
    * @param  string $name Name of route
    * @return string Url for given route name
    */
    public function routeUrl(string $name): string;
}