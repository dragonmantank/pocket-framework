<?php

declare(strict_types=1);

namespace PocketFramework\Framework\Router\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProcessController implements RequestHandlerInterface
{
    public function __construct(
        protected $controller,
        protected $vars,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $controller = $this->controller;
        return $controller($request, ...$this->vars);
    }
}
