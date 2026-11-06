<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(array $payload, int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        ), PHP_EOL;
    }

    public static function success(mixed $data = null, string $message = 'Berhasil', int $status = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function error(string $message, int $status = 400, array $extra = []): void
    {
        self::json(array_merge([
            'success' => false,
            'message' => $message,
        ], $extra), $status);
    }
}
