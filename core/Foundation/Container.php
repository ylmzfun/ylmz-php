<?php

namespace Ylmz\Foundation;

use Closure;
use ReflectionClass;
use ReflectionParameter;

class Container
{
    private static ?self $instance = null;
    private array $bindings = [];
    private array $instances = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function setInstance(?self $container): void
    {
        self::$instance = $container;
    }

    public function bind(string $abstract, Closure|string|null $concrete = null): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = null;
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function make(string $abstract): mixed
    {
        if (isset($this->instances[$abstract]) && $this->instances[$abstract] !== null) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;

        if ($concrete instanceof Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->build($concrete);
        }

        if (array_key_exists($abstract, $this->instances)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    public function build(string $concrete): object
    {
        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new \RuntimeException("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = array_map(
            fn(ReflectionParameter $param) => $this->resolveParameter($param),
            $constructor->getParameters()
        );

        return $reflector->newInstanceArgs($dependencies);
    }

    private function resolveParameter(ReflectionParameter $param): mixed
    {
        $type = $param->getType();

        if ($type && !$type->isBuiltin()) {
            return $this->make($type->getName());
        }

        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        throw new \RuntimeException("Cannot resolve parameter: {$param->getName()}");
    }

    private function __clone() {}
}
