<?php

declare(strict_types=1);

namespace PocketFramework\Framework\Router;

use FastRoute\RouteCollector;

class RouteProcessor
{
    public function __construct(protected string $path, protected string $namespace)
    {
    }

    public function addRoutes(RouteCollector $r)
    {
        $dir = new \RecursiveDirectoryIterator($this->path);
        $iterator = new \RecursiveIteratorIterator($dir);
        $files = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($files as $file) {
            $className = pathinfo(substr($file[0], strlen($this->path) + 1), PATHINFO_FILENAME);
            $folderInfo = explode(DIRECTORY_SEPARATOR, substr($file[0], strlen($this->path) + 1));
            array_pop($folderInfo);
            $combined = implode('\\', $folderInfo);
            if ($combined) {
                $class = $this->namespace . $combined . "\\" . $className;
            } else {
                $class = $this->namespace . $className;
            }

            $refClass = new \ReflectionClass($class);
            $attributes = $refClass->getAttributes(RouteInfo::class);
            if ($attributes) {
                $instance = $attributes[0]->newInstance();
                $r->addRoute($instance->methods, $instance->route, $class);
            }

            $baseRoute = '';
            $baseRouteAttribute = $refClass->getAttributes(RouteGroup::class);
            if ($baseRouteAttribute) {
                $instance = $baseRouteAttribute[0]->newInstance();
                $baseRoute = $instance->routeBase;
            }

            foreach ($refClass->getMethods() as $method) {
                $attributes = $method->getAttributes(RouteInfo::class);
                if ($attributes) {
                    $instance = $attributes[0]->newInstance();
                    $r->addRoute($instance->methods, $baseRoute . $instance->route, $class . '::' .  $method->getName());
                }
            }
        }
    }
}
