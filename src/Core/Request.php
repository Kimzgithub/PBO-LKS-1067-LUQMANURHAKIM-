<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\HttpException;

final class Request
{
    private string $method;
    private string $path;
    private array $query;
    private ?array $body = null;

    public function __construct(?string $method = null, ?string $uri = null)
    {
        $this->method = strtoupper($method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        $uri = $uri ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        $path = rawurldecode(is_string($path) ? $path : '/');

        $this->path  = $path === '/' ? '/' : rtrim($path, '/');
        $this->query = $_GET;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    /**
     * Body request sebagai array. Mendukung JSON (utama) dan form-urlencoded.
     *
     * @throws HttpException 400 bila body bukan JSON yang valid
     */
    public function body(): array
    {
        if ($this->body !== null) {
            return $this->body;
        }

        if (!empty($_POST)) {
            return $this->body = $_POST;
        }

        $raw = trim((string) file_get_contents('php://input'));
        if ($raw === '') {
            return $this->body = [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new HttpException('Body request harus berupa JSON yang valid', 400);
        }

        return $this->body = $decoded;
    }
}
