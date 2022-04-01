# Pocket Framework
#### A small PHP framework that tries to get out of the way

Pocket Framework is a small PHP framework that is designed to get out of the way. Write your code using plain PHP Objects for almost anything, sprinkle in a few attributes, and you are all set!

## Installation

Installation is done using composer:

```console
composer require pocket-framework/framework
```

### Requirements
- PHP 8.1 or higher

## Usage

Pocket Framework requires a small bootstrapping script to get running, as the framework requires an HTTP dispatcher (`nikic/fast-route` is included) as well as a PSR-11-compatible service container. We recommend using [`php-di/php-di`](https://php-di.org/).

```php
// index.php
<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use FastRoute\RouteCollector;
use PocketFramework\Framework\Application;
use PocketFramework\Framework\Router\RouteProcessor;

session_start();
require_once __DIR__ . '/../vendor/autoload.php';

// Just pulls in a PHP-DI definition file, nothing specific to Pocket Framework
$container = (new ContainerBuilder())
    ->addDefinitions(require_once __DIR__ . '/../config/di.php')
    ->build();

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    // Set the folder your controllers/actions are stored in
    $processor = new RouteProcessor(realpath(__DIR__ . '/../src/Action'), "My\\WebApp\\Action\\");
    $processor->addRoutes($r);
});

$app = new Application($dispatcher, $container);
$app->run();
```

## Configuration

Pocket Framework currently does not have much configuration outside of defining your PSR-11 service container (which is done on a library-by-library basis), and telling the `PocketFramework\Framework\Router\RouteProcessor` where to look for your controller and actions. Otherwise you declare your controllers (if you are using the Model-View-Controller pattern) or Actions (if you are using Action-Domain-Responder pattern) via PHP attributes.

```php
// Setting up the nikic/fast-route Dispatcher
$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    // Set the folder your controllers/actions are stored in
    $processor = new RouteProcessor(realpath(__DIR__ . '/../src/Action'), "My\\WebApp\\Action\\");
    $processor->addRoutes($r);
});
```

If you have PSR-15 middleware you can add them to the routing stack as well. These middlewares can help with authentication, logging, or anything else you need to do before a route is dispatched.

```php
$application->addMiddleware(new My\WebApp\Middleware\CustomMiddleware());
```

### Declaring Routes

Pocket Framework allows you to declare routes as PHP attributes on generic PHP objects, using either the Model-View-Controller (MVC) pattern or the Action-Domain-Responder (ADR) pattern. You can even mix-and-match! The only difference is where the attributes live.

Pocket Framework ships with two attributes - `PocketFramework\Framework\Router\RouteGroup` and `PocketFramework\Framework\Router\RouteInfo`. The `RouteInfo` attribute declares that a method or class should be attached to a specific route, and lets you define the path, methods, and any route-specific middleware that is needed. `RouteGroup` is used to define a base-route for a class that contains multiple routes that share a common base URI.

If you are using ADR, where one class generally handles one action or route, you can use `RouteInfo` directly on the invokable class to map it to a route. Pocket Framework will then invoke that class when the route is accessed.

```php
use PocketFramework\Framework\Router\RouteInfo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// Maps this invokable class to /example
#[RouteInfo(route: '/example', methods: ['GET'])]
class MyController
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['status' => 1]);
    }
}
```

If you are using MVC, where an object may handle multiple routes, you can instead use `RouteInfo` on a class method to map the method to a route. If you combine it with a `RouteGroup` attribute, all the routes in the class will share a common base URI.

```php
use PocketFramework\Framework\Router\RouteInfo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// Prepends /example to all routes inside this class
#[RouteGroup(routeBase: '/example')]
class MyController
{
    // Maps this method to /example/
    #[RouteInfo(route: '/', methods: ['GET'])]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['status' => 1]);
    }

    // Maps this method to /example/test
    #[RouteInfo(route: '/test', methods: ['GET'])]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['status' => 1]);
    }
}
```

Both `RouteGroup` and `RouteInfo` allow you to have dynamic chunks in the URL. You can add `{<identifier>:<regex>}` in the URL to specify a value to capture and compare against. This value will be passed to the associated route for use in your business logic. Keep in mind that no matter the regex, the value is passed into the controller as a string as a URL itself is a string.

```php
use PocketFramework\Framework\Router\RouteInfo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// Maps this invokable class to /user/<number>
#[RouteInfo(route: '/user/{id:\d+}', methods: ['GET'])]
class MyController
{
    public function __invoke(ServerRequestInterface $request, string $id): ResponseInterface
    {
        return new JsonResponse(['status' => 1]);
    }
}
```