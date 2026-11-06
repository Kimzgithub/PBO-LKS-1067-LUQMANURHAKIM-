<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\BookService;

/**
 * Pintu masuk HTTP untuk resource Buku. Tipis: hanya menerjemahkan
 * request -> panggilan service -> response JSON.
 */
final class BookController
{
    public function __construct(private BookService $service)
    {
    }

    public function index(Request $request, array $params = []): void
    {
        Response::success($this->service->list(), 'Daftar buku');
    }

    public function show(Request $request, array $params = []): void
    {
        Response::success($this->service->find((int) $params['id']), 'Detail buku');
    }

    public function store(Request $request, array $params = []): void
    {
        $book = $this->service->create($request->body());

        Response::success($book, 'Buku berhasil dibuat', 201);
    }

    public function update(Request $request, array $params = []): void
    {
        $book = $this->service->update((int) $params['id'], $request->body());

        Response::success($book, 'Buku berhasil diperbarui');
    }

    public function destroy(Request $request, array $params = []): void
    {
        $this->service->delete((int) $params['id']);

        Response::success(null, 'Buku berhasil dihapus');
    }
}
