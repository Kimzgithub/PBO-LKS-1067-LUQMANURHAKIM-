<?php

// Langkah 7 - Verifikasi Router + Container.
// Jalankan dari root proyek:  php tests/manual/test_router.php

$root = dirname(__DIR__, 2);

require $root . '/vendor/autoload.php';

use App\Core\Container;
use App\Core\Request;
use App\Core\Router;

// Simulasi URI
$_SERVER['REQUEST_URI'] = '/api/hello';
$_SERVER['REQUEST_METHOD'] = 'GET';

$router = new Router();
$router->get('/api/hello', function () {
    echo "✅ Router bekerja! Endpoint /api/hello terpanggil." . PHP_EOL;
});

$router->dispatch(new Request());

// Container: bind() dan make()
$container = new Container();
$container->bind('salam', fn () => 'Halo dari Container!');
echo "✅ Container bekerja! " . $container->make('salam') . PHP_EOL;
