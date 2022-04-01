<?php

declare(strict_types=1);

namespace PocketFramework\Framework\Router\Middleware;

use FastRoute\Dispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Router implements RequestHandlerInterface
{
    public function __construct(
        protected Dispatcher $dispatcher,
        protected ContainerInterface $container,
        protected StackHandler $stackHandler
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->stackHandler->handle($request);
        if ($response) {
            return $response;
        }
    }
}
