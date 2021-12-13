<?php

declare(strict_types=1);

namespace PhpWeb\Middleware;

use Laminas\Diactoros\Response;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
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
    public const ATTRIBUTE_LOG = 'log';
    public const ATTRIBUTE_LOG_NAME = 'name';
    public const ATTRIBUTE_LOG_FILE = 'file';
    public const ATTRIBUTE_THROWABLE = 'throwable';
    public const ATTRIBUTE_NOTFOUND = 'notfound';
    

    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

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
        $notfound = $this->config[self::ATTRIBUTE_NOTFOUND] ?? null;
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
        $log = $this->config[self::ATTRIBUTE_LOG] ?? null;
        if($log){
            $logger = new Logger($log[self::ATTRIBUTE_LOG_NAME]);
            $logger->pushHandler(new StreamHandler($log[self::ATTRIBUTE_LOG_FILE]));
            $logger->pushHandler(new FirePHPHandler());
            $logger->error($exception->getMessage());
        }

        $throwable = $this->config[self::ATTRIBUTE_THROWABLE] ?? null;
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
