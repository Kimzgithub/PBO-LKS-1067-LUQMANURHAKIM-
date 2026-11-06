<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\BookRepositoryInterface;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Book;

/**
 * Logika bisnis buku: validasi + koordinasi repository.
 * Tidak tahu apa-apa soal HTTP maupun SQL (SRP + DIP).
 */
final class BookService
{
    public function __construct(private BookRepositoryInterface $repository)
    {
    }

    public function list(): array
    {
        return array_map(
            fn (Book $book) => $book->toArray(),
            $this->repository->findAll()
        );
    }

    public function find(int $id): array
    {
        return $this->findOrFail($id)->toArray();
    }

    public function create(array $data): array
    {
        $book = $this->validate($data);

        return $this->repository->create($book)->toArray();
    }

    public function update(int $id, array $data): array
    {
        $this->findOrFail($id);

        $book = $this->validate($data, $id);
        $this->repository->update($book);

        return $book->toArray();
    }

    public function delete(int $id): void
    {
        $this->findOrFail($id);
        $this->repository->delete($id);
    }

    private function findOrFail(int $id): Book
    {
        $book = $this->repository->findById($id);

        if ($book === null) {
            throw new NotFoundException("Buku dengan ID {$id} tidak ditemukan");
        }

        return $book;
    }

    /**
     * @throws ValidationException pada kesalahan pertama yang ditemukan
     */
    private function validate(array $data, ?int $id = null): Book
    {
        $judul = trim((string) ($data['judul'] ?? ''));
        if ($judul === '') {
            throw new ValidationException('Judul wajib diisi');
        }
        if (mb_strlen($judul) > 255) {
            throw new ValidationException('Judul maksimal 255 karakter');
        }

        $penulis = trim((string) ($data['penulis'] ?? ''));
        if ($penulis === '') {
            throw new ValidationException('Penulis wajib diisi');
        }
        if (mb_strlen($penulis) > 255) {
            throw new ValidationException('Penulis maksimal 255 karakter');
        }

        $tahunMax = (int) date('Y') + 1;
        $tahun = $this->toInt($data['tahun'] ?? null);
        if ($tahun === null || $tahun < 1000 || $tahun > $tahunMax) {
            throw new ValidationException("Tahun harus berupa angka antara 1000 dan {$tahunMax}");
        }

        // Stok bersifat opsional: bila tidak dikirim, dianggap 0.
        $stok = array_key_exists('stok', $data) && $data['stok'] !== '' && $data['stok'] !== null
            ? $this->toInt($data['stok'])
            : 0;
        if ($stok === null || $stok < 0) {
            throw new ValidationException('Stok harus berupa angka bulat tidak negatif');
        }

        return new Book($id, $judul, $penulis, $tahun, $stok);
    }

    /** Mengubah int / string angka bulat menjadi int; selain itu null. */
    private function toInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', trim($value)) === 1) {
            return (int) trim($value);
        }

        return null;
    }
}
