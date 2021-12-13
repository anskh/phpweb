<?php

declare(strict_types=1);

use FastRoute\RouteCollector;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use PhpWeb\Config\Environment;
use PhpWeb\Http\Kernel;
use PhpWeb\Middleware\AccessControlMiddleware;
use PhpWeb\Middleware\ExceptionHandlerMiddleware;
use PhpWeb\Middleware\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WoohooLabs\Harmony\Middleware\DispatcherMiddleware;
use WoohooLabs\Harmony\Middleware\FastRouteMiddleware;
use WoohooLabs\Harmony\Middleware\LaminasEmitterMiddleware;

use function FastRoute\simpleDispatcher;

require dirname(__DIR__) . '/vendor/autoload.php';

$router = simpleDispatcher(static function(RouteCollector $r){
    $baseUrl = '/phpweb/example';
    $r->get($baseUrl . '/', static function(ServerRequestInterface $request, ResponseInterface $response){
        $response->getBody()->write('Hello');

        return $response;
    });
});

Kernel::init(dirname(__DIR__) . '/config', Environment::DEVELOPMENT);
$app = Kernel::handle(ServerRequestFactory::fromGlobals(), new Response());
$app->addMiddleware(new LaminasEmitterMiddleware(new SapiEmitter()))
    ->addMiddleware(new ExceptionHandlerMiddleware())
    ->addMiddleware(new SessionMiddleware())
    ->addMiddleware(new AccessControlMiddleware())
    ->addMiddleware(new FastRouteMiddleware($router))
    ->addMiddleware(new DispatcherMiddleware())
    ->run();
