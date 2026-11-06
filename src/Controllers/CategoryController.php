<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\CategoryService;

final class CategoryController
{
    public function __construct(private CategoryService $service)
    {
    }

    public function index(Request $request, array $params = []): void
    {
        Response::success($this->service->list(), 'Daftar kategori');
    }

    public function show(Request $request, array $params = []): void
    {
        Response::success($this->service->find((int) $params['id']), 'Detail kategori');
    }

    public function store(Request $request, array $params = []): void
    {
        $category = $this->service->create($request->body());

        Response::success($category, 'Kategori berhasil dibuat', 201);
    }

    public function update(Request $request, array $params = []): void
    {
        $category = $this->service->update((int) $params['id'], $request->body());

        Response::success($category, 'Kategori berhasil diperbarui');
    }

    public function destroy(Request $request, array $params = []): void
    {
        $this->service->delete((int) $params['id']);

        Response::success(null, 'Kategori berhasil dihapus');
    }
}
