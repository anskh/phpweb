# PhpWeb

Simple PHP Website Foundation based on PSR using Harmony as RequestHandler

## Install PhpWeb

To install the latest version of this library, run the command below:

```bash
$   composer require anskh/phpweb
```
## Init Application

You can init Application and Configuration using:

```
Kernel::init($rootDir, $configDir, $appAttribute, $environment);
```
## Get Application Instance

To get Application instance:

```php
$app    = Kernel::app();
```

$rootDir is root directory, $configDir is config directory, $appAttribute is file name in $configDir that contains Application configuration, default is 'app'. $environment is mode of Environment, can be 'development' or 'production'.

## Create Request Handler (Harmony)

You can create request handler (harmony) using:

```php
$handler    = $app->handle(ServerRequestFactory::fromGlobals(), new Response(), $routeAttribute);
```

Param 1 is ServerRequestInterface, param 2 is ResponseInterface, and $routeAttribute is file name in $configDir that define route definitions, for example is 'route'.

## Get Dispatcher

We use FastRoute Dispatcher, ang can be access below:

```php
$dispatcher = $app->router()->getDispatcher();
```

## Add Middleware and run

to add and run middleware for example:

```php
$handler
    ->addMiddleware(new LaminasEmitterMiddleware(new SapiEmitter()))
    ->addMiddleware(new ExceptionHandlerMiddleware($exceptionAttribute))
    ->addMiddleware(new SessionMiddleware())
    ->addMiddleware(new AccessControlMiddleware($accesscontrolAttribute))
    ->addMiddleware(new FastRouteMiddleware($dispatcher))
    ->addMiddleware(new DispatcherMiddleware())
    ->run();
```

$exceptionAttribute is file name in $configDir define exception and $accesscontrolAttribute is file name in $configDir define accesscontrol.

## Helper

You can access user identity using:

```php
Kernel::app()->user();
or
my_app()->user();
```

You can access session using 

```php
Kernel::app()->session();
or
my_app()->session();
```

You can access config data using dot notation using 

```php
Kernel::config()->get('app.default_connection');
or
my_config()->get('app.default_connection');
```

For more detail you can see in example folder.