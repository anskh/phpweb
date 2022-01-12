<?php

declare(strict_types=1);

namespace Anskh\PhpWeb\Middleware;

namespace Anskh\PhpWeb\Middleware;

use Anskh\PhpWeb\Http\AppException;
use Anskh\PhpWeb\Http\Environment;
use Laminas\Diactoros\Response;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use WoohooLabs\Harmony\Exception\MethodNotAllowed;
use WoohooLabs\Harmony\Exception\RouteNotFound;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

/**
* ExceptionHandlerMiddleware
*
* @package    Anskh\PhpWeb\Middleware
* @author     Khaerul Anas <anasikova@gmail.com>
* @copyright  2021-2022 Anskh Labs.
* @version    1.0.0
*/
final class ExceptionHandlerMiddleware implements MiddlewareInterface
{
    private string $exceptionAttribute;

    /**
    * Constructor
    *
    * @param  string $exceptionAttribute exception attribute, default is 'exception'
    * @return void
    */
    public function __construct(string $exceptionAttribute = 'exception')
    {
        $this->exceptionAttribute = $exceptionAttribute;
    }

    /**
     * @inheritdoc
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
    * Handle not found
    *
    * @return ResponseInterface response
    */
    private function handleNotFound(): ResponseInterface
    {
        $attributeNotFound = AppException::ATTR_NOTFOUND;
        $notfound = my_config()->get("{$this->exceptionAttribute}.{$attributeNotFound}");
        if ($notfound && is_callable($notfound)) {
            return $notfound();
        } else {
            $response = new Response();
            $response->getBody()->write("<h1>Error 404</h1> <p>Page was not found!</p>");

            return $response->withStatus(404);
        }
    }

    /**
    * Handle throwable
    *
    * @param  Throwable $exception Throwable
    * @return ResponseInterface response
    */
    private function handleThrowable(Throwable $exception): ResponseInterface
    {
        $attributeEnableLog = AppException::ATTR_LOG_ENABLE;
        $logEnable = my_config()->get("{$this->exceptionAttribute}.{$attributeEnableLog}");
        if($logEnable){
            $attributeLogName = AppException::ATTR_LOG_NAME;
            $logName = my_config()->get("{$this->exceptionAttribute}.{$attributeLogName}");
            $logger = new Logger($logName);
            $attributeLogFile= AppException::ATTR_LOG_FILE;
            $logFile = my_config()->get("{$this->exceptionAttribute}.{$attributeLogFile}");
            $logger->pushHandler(new StreamHandler($logFile));
            $logger->pushHandler(new FirePHPHandler());
            $logger->error($exception->getMessage());
        }

        $attributeThrowable = AppException::ATTR_THROWABLE;
        $throwable = my_config()->get("{$this->exceptionAttribute}.{$attributeThrowable}");
        if($throwable && is_callable($throwable)){
            return $throwable();
        }else{
            $response = new Response();
            if(my_config()->getEnvironment() === Environment::DEVELOPMENT){
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