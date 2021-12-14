<?php

declare(strict_types=1);

namespace PhpWeb\Middleware;

use Laminas\Diactoros\Response;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PhpWeb\Config\Config;
use PhpWeb\Config\Environment;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use WoohooLabs\Harmony\Exception\MethodNotAllowed;
use WoohooLabs\Harmony\Exception\RouteNotFound;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

use function PhpWeb\app;

final class ExceptionHandlerMiddleware implements MiddlewareInterface
{
    /**
     * 
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (RouteNotFound | MethodNotAllowed $exception) {
            return $this->handleNotFound();
        } catch (Throwable $exception) {
            return $this->handleThrowable($exception);
        }
    }

    /**
     * 
     */
    private function handleNotFound(): ResponseInterface
    {
        $notfound = app()->config(Config::ATTR_EXCEPTION_CONFIG . '.' . Config::ATTR_EXCEPTION_NOTFOUND);
        if ($notfound && is_callable($notfound)) {
            return $notfound();
        } else {
            $response = new Response();
            $response->getBody()->write("<h1>Error 404</h1> <p>Page was not found!</p>");

            return $response->withStatus(404);
        }
    }

    /**
     * 
     */
    private function handleThrowable(Throwable $exception): ResponseInterface
    {
        $log = app()->config(Config::ATTR_EXCEPTION_CONFIG . '.' . Config::ATTR_EXCEPTION_LOG);
        if($log){
            $logger = new Logger($log[Config::ATTR_EXCEPTION_LOG_NAME]);
            $logger->pushHandler(new StreamHandler($log[Config::ATTR_EXCEPTION_LOG_FILE]));
            $logger->pushHandler(new FirePHPHandler());
            $logger->error($exception->getMessage());
        }

        $throwable = app()->config(Config::ATTR_EXCEPTION_CONFIG . '.' . Config::ATTR_EXCEPTION_THROWABLE);
        if($throwable && is_callable($throwable)){
            return $throwable();
        }else{
            $response = new Response();
            if(app()->environment === Environment::DEVELOPMENT){
                $whoops = new Run();
                $whoops->allowQuit(false);
                $whoops->writeToOutput(false);
                $whoops->pushHandler(new PrettyPageHandler());
                $output = $whoops->handleException($exception);
                
                $response->getBody()->write($output);
            }else{
                $response->getBody()->write('<h1>Error 500</h1> <p>' . $exception->getMessage() . '</p>');
            }
            
            return $response->withStatus(500);
        }
    }
}