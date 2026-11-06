<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CategoryRepositoryInterface;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Category;

final class CategoryService
{
    public function __construct(private CategoryRepositoryInterface $repository)
    {
    }

    public function list(): array
    {
        return array_map(
            fn (Category $category) => $category->toArray(),
            $this->repository->findAll()
        );
    }

    public function find(int $id): array
    {
        return $this->findOrFail($id)->toArray();
    }

    public function create(array $data): array
    {
        $category = $this->validate($data);

        return $this->repository->create($category)->toArray();
    }

    public function update(int $id, array $data): array
    {
        $this->findOrFail($id);

        $category = $this->validate($data, $id);
        $this->repository->update($category);

        return $category->toArray();
    }

    public function delete(int $id): void
    {
        $this->findOrFail($id);
        $this->repository->delete($id);
    }

    private function findOrFail(int $id): Category
    {
        $category = $this->repository->findById($id);

        if ($category === null) {
            throw new NotFoundException("Kategori dengan ID {$id} tidak ditemukan");
        }

        return $category;
    }

    private function validate(array $data, ?int $id = null): Category
    {
        $nama = trim((string) ($data['nama'] ?? ''));
        if ($nama === '') {
            throw new ValidationException('Nama kategori wajib diisi');
        }
        if (mb_strlen($nama) > 100) {
            throw new ValidationException('Nama kategori maksimal 100 karakter');
        }

        $deskripsi = isset($data['deskripsi']) ? trim((string) $data['deskripsi']) : null;
        if ($deskripsi === '') {
            $deskripsi = null;
        }

        return new Category($id, $nama, $deskripsi);
    }
}
