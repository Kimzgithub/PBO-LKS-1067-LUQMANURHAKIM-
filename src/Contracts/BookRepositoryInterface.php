<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Book;

/**
 * Kontrak akses data buku (DIP: Service bergantung pada abstraksi ini,
 * bukan pada implementasi MySQL).
 */
interface BookRepositoryInterface
{
    /** @return Book[] */
    public function findAll(): array;

    public function findById(int $id): ?Book;

    /** Menyimpan buku baru, mengembalikan Book lengkap dengan ID. */
    public function create(Book $book): Book;

    /** Memperbarui buku berdasarkan ID yang ada di dalam objek Book. */
    public function update(Book $book): void;

    /** @return bool true bila ada baris yang terhapus */
    public function delete(int $id): bool;
}
