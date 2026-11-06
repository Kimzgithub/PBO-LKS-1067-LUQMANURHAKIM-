<?php

// Langkah 5 - Verifikasi MySQLBookRepository.
// Jalankan dari root proyek:  php tests/manual/test_repository.php

$root = dirname(__DIR__, 2);

require $root . '/vendor/autoload.php';

use App\Config\Env;
use App\Database\Connection;
use App\Repositories\MySQLBookRepository;

Env::load($root . '/.env');
$pdo = Connection::get();
$repo = new MySQLBookRepository($pdo);

$books = $repo->findAll();
echo "Total buku: " . count($books) . PHP_EOL;
foreach ($books as $book) {
    echo "- {$book->getJudul()} oleh {$book->getPenulis()}" . PHP_EOL;
}
