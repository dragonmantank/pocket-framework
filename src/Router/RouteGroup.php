<?php

declare(strict_types=1);

namespace PocketFramework\Framework\Router;

#[\Attribute(\Attribute::TARGET_CLASS)]
class RouteGroup
{
    public function __construct(
        public readonly string $routeBase,
    ) {
    }
}
