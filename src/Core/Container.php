<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
    /** @var array<string, array{concrete: Closure|string|null, shared: bool}> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    public function bind(string $abstract, Closure|string|null $concrete = null, bool $shared = false): void
    {
        $this->bindings[$abstract] = ['concrete' => $concrete, 'shared' => $shared];
        unset($this->instances[$abstract]);
    }

    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    public function make(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $binding  = $this->bindings[$abstract] ?? ['concrete' => null, 'shared' => false];
        $concrete = $binding['concrete'] ?? $abstract;

        if ($concrete instanceof Closure) {
            $object = $concrete($this);
        } elseif ($concrete !== $abstract) {
            $object = $this->make($concrete);
        } else {
            $object = $this->build($concrete);
        }

        if ($binding['shared']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function build(string $class): object
    {
        if (!class_exists($class)) {
            throw new RuntimeException("Kelas atau binding tidak ditemukan: {$class}");
        }

        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException(
                "{$class} tidak bisa diinstansiasi (interface/abstract belum di-bind di Container)"
            );
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return new $class();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $arguments[] = $this->make($type->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
            } else {
                throw new RuntimeException(
                    "Tidak bisa me-resolve parameter \${$parameter->getName()} pada {$class}"
                );
            }
        }

        return $reflection->newInstanceArgs($arguments);
    }
}
