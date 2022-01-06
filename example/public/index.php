<?php

declare(strict_types=1);

if(!defined("ROOT")) define("ROOT", dirname(__DIR__));

require_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";

use Anskh\PhpWeb\Middleware\AccessControlMiddleware;
use Anskh\PhpWeb\Middleware\ExceptionHandlerMiddleware;
use Anskh\PhpWeb\Middleware\SessionMiddleware;
use Laminas\Diactoros\{
    Response,
    ServerRequestFactory
};
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Anskh\PhpWeb\Config\Config;
use Anskh\PhpWeb\Config\Environment;
use Anskh\PhpWeb\Http\Kernel;
use WoohooLabs\Harmony\Middleware\{
    DispatcherMiddleware,
    FastRouteMiddleware,
    LaminasEmitterMiddleware
};

// Initializing config
Kernel::init(ROOT . '/config', Environment::DEVELOPMENT);

// Initializing the router
$router = FastRoute\simpleDispatcher(static function (FastRoute\RouteCollector $r) {
    $base_path = Kernel::getInstance()->config(Config::ATTR_APP_CONFIG . '.' . Config::ATTR_APP_BASEPATH, '');
    $routes = Kernel::getInstance()->config(Config::ATTR_ROUTE_CONFIG, []);

    foreach($routes as $route){
        list($method, $path, $handler) = $route;
        $r->addRoute($method, $base_path . $path, $handler);
    }
});

// Instantiating the framework
$app = Kernel::handle(ServerRequestFactory::fromGlobals(), new Response());

// Stacking up middleware
$app
    ->addMiddleware(new LaminasEmitterMiddleware(new SapiEmitter()))
    ->addMiddleware(new ExceptionHandlerMiddleware())
    ->addMiddleware(new SessionMiddleware())
    ->addMiddleware(new AccessControlMiddleware())
    ->addMiddleware(new FastRouteMiddleware($router))
    ->addMiddleware(new DispatcherMiddleware())
    ->run();