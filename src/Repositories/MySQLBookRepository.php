<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\BookRepositoryInterface;
use App\Models\Book;
use PDO;

/**
 * Implementasi konkret BookRepositoryInterface menggunakan PDO + MySQL.
 */
final class MySQLBookRepository implements BookRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, judul, penulis, tahun, stok FROM books ORDER BY judul ASC'
        );

        return array_map(fn (array $row) => $this->hydrate($row), $stmt->fetchAll());
    }

    public function findById(int $id): ?Book
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, judul, penulis, tahun, stok FROM books WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    public function create(Book $book): Book
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO books (judul, penulis, tahun, stok)
             VALUES (:judul, :penulis, :tahun, :stok)'
        );
        $stmt->execute([
            'judul'   => $book->getJudul(),
            'penulis' => $book->getPenulis(),
            'tahun'   => $book->getTahun(),
            'stok'    => $book->getStok(),
        ]);

        return new Book(
            (int) $this->pdo->lastInsertId(),
            $book->getJudul(),
            $book->getPenulis(),
            $book->getTahun(),
            $book->getStok()
        );
    }

    public function update(Book $book): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE books
                SET judul = :judul, penulis = :penulis, tahun = :tahun, stok = :stok
              WHERE id = :id'
        );
        $stmt->execute([
            'id'      => $book->getId(),
            'judul'   => $book->getJudul(),
            'penulis' => $book->getPenulis(),
            'tahun'   => $book->getTahun(),
            'stok'    => $book->getStok(),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM books WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    private function hydrate(array $row): Book
    {
        return new Book(
            (int) $row['id'],
            (string) $row['judul'],
            (string) $row['penulis'],
            (int) $row['tahun'],
            (int) $row['stok']
        );
    }
}
