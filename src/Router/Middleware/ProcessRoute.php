<?php

declare(strict_types=1);

namespace PocketFramework\Framework\Router\Middleware;

use FastRoute\Dispatcher;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\TextResponse;
use PocketFramework\Framework\Router\Middleware\ProcessMethod;
use PocketFramework\Framework\Router\RouteInfo;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProcessRoute implements RequestHandlerInterface
{
    public function __construct(
        protected Dispatcher $dispatcher,
        protected ContainerInterface $container
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response = new EmptyResponse(404);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $response = new TextResponse(implode(', ', $allowedMethods), 405);
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                if (is_string($handler)) {
                    $class = $handler;
                    $method = null;

                    if (strpos($handler, '::') !== false) {
                        [$class, $method] = explode('::', $handler);
                    }

                    $controller = $this->container->get($class);
                    $refClass = new \ReflectionClass($controller);
                    $attributes = $refClass->getAttributes(RouteInfo::class);
                    if ($attributes) {
                        $instance = $attributes[0]->newInstance();
                        if ($instance->preMiddleware) {
                            $stack = new StackHandler(new ProcessController($controller, $vars));
                            foreach ($instance->preMiddleware as $middleware) {
                                if (is_string($middleware)) {
                                    $middleware = $this->container->get($middleware);
                                }
                                $stack->add($middleware);
                            }
                            $response = $stack->handle($request);
                        } else {
                            if (!$method) {
                                $response = $controller($request, ...$vars);
                            } else {
                                $response = call_user_func([$controller, $method], $request, ...$vars);
                            }
                        }
                    } else {
                        if (!$method) {
                            $response = $controller($request, ...$vars);
                        } else {
                            $refMethod = $refClass->getMethod($method);
                            $attributes = $refMethod->getAttributes(RouteInfo::class);
                            if ($attributes) {
                                $instance = $attributes[0]->newInstance();
                                if ($instance->preMiddleware) {
                                    $stack = new StackHandler(new ProcessMethod($controller, $method, $vars));
                                    foreach ($instance->preMiddleware as $middleware) {
                                        if (is_string($middleware)) {
                                            $middleware = $this->container->get($middleware);
                                        }
                                        $stack->add($middleware);
                                    }
                                    $response = $stack->handle($request);
                                } else {
                                    $response = call_user_func([$controller, $method], $request, ...$vars);
                                }
                            } else {
                                $response = call_user_func([$controller, $method], $request, ...$vars);
                            }
                        }
                    }
                }

                break;
        }

        return $response;
    }
}
