<?php

declare(strict_types=1);

namespace App\Config;

use RuntimeException;

/**
 * Loader file .env sederhana (SRP: hanya membaca & menyediakan konfigurasi).
 */
final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException(
                "File .env tidak ditemukan di: {$path}. Salin dari .env.example terlebih dahulu."
            );
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = self::clean(trim($value));

            // Variabel environment asli sistem tidak ditimpa.
            if (getenv($key) !== false) {
                continue;
            }

            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);

        return ($value === false || $value === null) ? $default : (string) $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    private static function clean(string $value): string
    {
        // Nilai dalam tanda kutip: ambil isi di dalam kutip.
        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            $quote = $value[0];
            $end = strrpos($value, $quote);

            if ($end !== false && $end > 0) {
                return substr($value, 1, $end - 1);
            }
        }

        // Nilai tanpa kutip: buang komentar inline (" # ...").
        $pos = strpos($value, ' #');
        if ($pos !== false) {
            $value = rtrim(substr($value, 0, $pos));
        }

        return $value;
    }
}
