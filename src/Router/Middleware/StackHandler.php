<?php

declare(strict_types=1);

namespace PocketFramework\Framework\Router\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StackHandler implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    protected array $stack = [];

    public function __construct(protected RequestHandlerInterface $fallbackHandler)
    {
    }

    public function add(MiddlewareInterface $middlewareInterface)
    {
        $this->stack[] = $middlewareInterface;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (0 === count($this->stack)) {
            return $this->fallbackHandler->handle($request);
        }

        $middleware = array_shift($this->stack);
        return $middleware->process($request, $this);
    }
}
