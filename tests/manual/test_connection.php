<?php

// Langkah 4 - Verifikasi koneksi database.
// Jalankan dari root proyek:  php tests/manual/test_connection.php

$root = dirname(__DIR__, 2);

require $root . '/vendor/autoload.php';

use App\Config\Env;
use App\Database\Connection;

Env::load($root . '/.env');
$pdo = Connection::get();
echo "✅ Koneksi database berhasil!" . PHP_EOL;
echo "Versi MySQL: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
