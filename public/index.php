<?php

declare(strict_types=1);

use App\Config\Env;
use App\Contracts\BookRepositoryInterface;
use App\Contracts\CategoryRepositoryInterface;
use App\Controllers\BookController;
use App\Controllers\CategoryController;
use App\Core\Container;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Database\Connection;
use App\Repositories\MySQLBookRepository;
use App\Repositories\MySQLCategoryRepository;

// ---------------------------------------------------------------
// CORS (Langkah 16) - harus di paling atas, sebelum kode lain.
// Memungkinkan frontend di localhost:3000 mengakses backend :8000.
// ---------------------------------------------------------------
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Max-Age: 86400');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

// ---------------------------------------------------------------
// Front Controller + Composition Root:
// semua objek dirakit di sini, lalu request diserahkan ke Router.
// ---------------------------------------------------------------
try {
    Env::load(dirname(__DIR__) . '/.env');

    // --- Binding Container (Dependency Injection) ---
    $container = new Container();

    $container->singleton(PDO::class, fn () => Connection::get());

    $container->bind(
        BookRepositoryInterface::class,
        fn (Container $c) => new MySQLBookRepository($c->make(PDO::class))
    );
    $container->bind(
        CategoryRepositoryInterface::class,
        fn (Container $c) => new MySQLCategoryRepository($c->make(PDO::class))
    );
    // BookService, CategoryService, dan Controller di-autowire oleh Container.

    // --- Routing ---
    $router = new Router($container);

    $router->get('/api', function () {
        Response::success(['versi' => '1.0'], 'REST API Perpustakaan berjalan');
    });

    // Buku
    $router->get('/api/books', [BookController::class, 'index']);
    $router->get('/api/books/{id}', [BookController::class, 'show']);
    $router->post('/api/books', [BookController::class, 'store']);
    $router->put('/api/books/{id}', [BookController::class, 'update']);
    $router->delete('/api/books/{id}', [BookController::class, 'destroy']);

    // Kategori (Langkah 14)
    $router->get('/api/categories', [CategoryController::class, 'index']);
    $router->get('/api/categories/{id}', [CategoryController::class, 'show']);
    $router->post('/api/categories', [CategoryController::class, 'store']);
    $router->put('/api/categories/{id}', [CategoryController::class, 'update']);
    $router->delete('/api/categories/{id}', [CategoryController::class, 'destroy']);

    $router->dispatch(new Request());
} catch (Throwable $e) {
    error_log($e::class . ': ' . $e->getMessage());

    // $extra = Env::bool('APP_DEBUG') ? ['debug' => $e->getMessage()] : [];
    // Response::error('Terjadi kesalahan pada server', 500, $extra);
}
