<?php

declare(strict_types=1);

namespace PocketFramework\Framework;

use FastRoute\Dispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use PocketFramework\Framework\Router\Middleware\ProcessRoute;
use PocketFramework\Framework\Router\Middleware\StackHandler;

class Application implements RequestHandlerInterface
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
        $response = $this->handle($this->request);
        (new SapiEmitter())->emit($response);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handler->handle($this->request);
    }
}
