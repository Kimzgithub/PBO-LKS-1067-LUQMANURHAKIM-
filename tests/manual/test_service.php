<?php

// Langkah 6 - Verifikasi BookService (validasi + create).
// Jalankan dari root proyek:  php tests/manual/test_service.php

$root = dirname(__DIR__, 2);

require $root . '/vendor/autoload.php';

use App\Config\Env;
use App\Database\Connection;
use App\Repositories\MySQLBookRepository;
use App\Services\BookService;

Env::load($root . '/.env');
$pdo = Connection::get();
$service = new BookService(new MySQLBookRepository($pdo));

// Test 1: list
echo "Daftar buku: " . count($service->list()) . PHP_EOL;

// Test 2: validasi (harus error)
try {
    $service->create(['judul' => '']);
} catch (Exception $e) {
    echo "✅ Validasi berjalan: " . $e->getMessage() . PHP_EOL;
}

// Test 3: create valid
$new = $service->create([
    'judul'   => 'Pulang',
    'penulis' => 'Leila S. Chudori',
    'tahun'   => 2012,
    'stok'    => 4,
]);
echo "✅ Buku dibuat: {$new['judul']} (ID: {$new['id']})" . PHP_EOL;
