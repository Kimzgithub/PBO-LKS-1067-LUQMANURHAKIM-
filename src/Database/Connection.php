<?php

declare(strict_types=1);

namespace App\Database;

use App\Config\Env;
use PDO;

/**
 * Koneksi PDO bergaya Singleton: satu koneksi dipakai bersama.
 */
final class Connection
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                Env::get('DB_HOST', '127.0.0.1'),
                Env::get('DB_PORT', '3306'),
                Env::get('DB_DATABASE', 'perpustakaan')
            );

            self::$instance = new PDO(
                $dsn,
                Env::get('DB_USERNAME', 'root'),
                Env::get('DB_PASSWORD', ''),
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }

        return self::$instance;
    }
}
