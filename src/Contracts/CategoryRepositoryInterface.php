<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Category;

interface CategoryRepositoryInterface
{
    /** @return Category[] */
    public function findAll(): array;

    public function findById(int $id): ?Category;

    public function create(Category $category): Category;

    public function update(Category $category): void;

    /** @return bool true bila ada baris yang terhapus */
    public function delete(int $id): bool;
}
