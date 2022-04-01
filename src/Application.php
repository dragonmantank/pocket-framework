<?php

declare(strict_types=1);

namespace PocketFramework\Framework;

use FastRoute\Dispatcher;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use PocketFramework\Framework\Router\Middleware\ProcessRoute;
use PocketFramework\Framework\Router\Middleware\Router;
use PocketFramework\Framework\Router\Middleware\StackHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class Application
{
    protected ServerRequestInterface $request;
    protected StackHandler $stackHandler;

    public function __construct(
        protected Dispatcher $dispatcher,
        protected ContainerInterface $container
    ) {
        $this->request = ServerRequestFactory::fromGlobals();
        $this->stackHandler = new StackHandler(new ProcessRoute($this->dispatcher, $this->container));
    }

    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->stackHandler->add($middleware);
    }

    public function run()
    {
        $handler = new Router($this->dispatcher, $this->container, $this->stackHandler);
        $response = $handler->handle($this->request,);

        (new SapiEmitter())->emit($response);
    }
}
