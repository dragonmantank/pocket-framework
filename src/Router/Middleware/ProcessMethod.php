<?php

declare(strict_types=1);

namespace PocketFramework\Framework\Router\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProcessMethod implements RequestHandlerInterface
{
    public function __construct(
        protected $controller,
        protected $method,
        protected $vars,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $controller = $this->controller;
        $method = $this->method;
        return $controller->$method($request, ...$this->vars);
    }
}
