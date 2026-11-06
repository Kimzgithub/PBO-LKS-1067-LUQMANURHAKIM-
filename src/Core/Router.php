<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\HttpException;
use App\Exceptions\NotFoundException;

final class Router
{
    /** @var array<int, array{method: string, regex: string, handler: callable|array}> */
    private array $routes = [];

    public function __construct(private ?Container $container = null)
    {
    }

    public function get(string $path, callable|array $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): self
    {
        return $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): self
    {
        return $this->add('DELETE', $path, $handler);
    }

    public function add(string $method, string $path, callable|array $handler): self
    {
        $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', rtrim($path, '/') ?: '/');

        $this->routes[] = [
            'method'  => strtoupper($method),
            'regex'   => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];

        return $this;
    }

    public function dispatch(Request $request): void
    {
        try {
            $this->run($request);
        } catch (HttpException $e) {
            Response::error($e->getMessage(), $e->getStatusCode());
        }
    }

    private function run(Request $request): void
    {
        $allowed = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $request->path(), $matches) !== 1) {
                continue;
            }

            if ($route['method'] !== $request->method()) {
                $allowed[] = $route['method'];
                continue;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $this->invoke($route['handler'], $request, $params);

            return;
        }

        if ($allowed !== []) {
            if (!headers_sent()) {
                header('Allow: ' . implode(', ', array_unique($allowed)));
            }

            throw new HttpException(
                "Metode {$request->method()} tidak diizinkan untuk endpoint {$request->path()}",
                405
            );
        }

        throw new NotFoundException("Endpoint tidak ditemukan: {$request->path()}");
    }

    private function invoke(callable|array $handler, Request $request, array $params): void
    {
        if (is_array($handler) && is_string($handler[0] ?? null)) {
            $controller = $this->container
                ? $this->container->make($handler[0])
                : new $handler[0]();

            $handler = [$controller, $handler[1]];
        }

        call_user_func($handler, $request, $params);
    }
}
