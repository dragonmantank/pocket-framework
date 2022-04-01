<?php

declare(strict_types=1);

namespace PocketFramework\Framework\Router;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class RouteInfo
{
    public function __construct(
        public readonly string $route,
        public readonly array $methods = ['GET'],
        public readonly array $preMiddleware = [],
    ) {
    }
}
