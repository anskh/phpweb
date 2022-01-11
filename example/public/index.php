<?php

declare(strict_types=1);

if(!defined("ROOT")) define("ROOT", dirname(__DIR__));

require_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";

use Anskh\PhpWeb\Http\Environment;
use Anskh\PhpWeb\Middleware\AccessControlMiddleware;
use Anskh\PhpWeb\Middleware\ExceptionHandlerMiddleware;
use Anskh\PhpWeb\Middleware\SessionMiddleware;
use Laminas\Diactoros\{
    Response,
    ServerRequestFactory
};
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Anskh\PhpWeb\Http\Kernel;
use WoohooLabs\Harmony\Middleware\{
    DispatcherMiddleware,
    FastRouteMiddleware,
    LaminasEmitterMiddleware
};

// Initializing config and app
Kernel::init(ROOT, ROOT . '/config', 'app', Environment::DEVELOPMENT);

// Instantiating the framework
$app        = Kernel::app();
$handler    = $app->handle(ServerRequestFactory::fromGlobals(), new Response(), 'route');
$dispatcher = $app->router()->getDispatcher();

// Stacking up middleware
$handler
    ->addMiddleware(new LaminasEmitterMiddleware(new SapiEmitter()))
    ->addMiddleware(new ExceptionHandlerMiddleware('exception'))
    ->addMiddleware(new SessionMiddleware())
    ->addMiddleware(new AccessControlMiddleware('accesscontrol'))
    ->addMiddleware(new FastRouteMiddleware($dispatcher))
    ->addMiddleware(new DispatcherMiddleware())
    ->run();