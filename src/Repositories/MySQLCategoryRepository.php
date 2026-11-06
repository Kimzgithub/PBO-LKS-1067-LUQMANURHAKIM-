<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\CategoryRepositoryInterface;
use App\Exceptions\ConflictException;
use App\Models\Category;
use PDO;
use PDOException;

final class MySQLCategoryRepository implements CategoryRepositoryInterface
{
    /** Kode error MySQL untuk pelanggaran UNIQUE (duplicate entry). */
    private const ERR_DUPLICATE = 1062;

    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, nama, deskripsi FROM categories ORDER BY nama ASC'
        );

        return array_map(fn (array $row) => $this->hydrate($row), $stmt->fetchAll());
    }

    public function findById(int $id): ?Category
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nama, deskripsi FROM categories WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    public function create(Category $category): Category
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO categories (nama, deskripsi) VALUES (:nama, :deskripsi)'
            );
            $stmt->execute([
                'nama'      => $category->getNama(),
                'deskripsi' => $category->getDeskripsi(),
            ]);
        } catch (PDOException $e) {
            $this->rethrowDuplicate($e);
        }

        return new Category(
            (int) $this->pdo->lastInsertId(),
            $category->getNama(),
            $category->getDeskripsi()
        );
    }

    public function update(Category $category): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE categories SET nama = :nama, deskripsi = :deskripsi WHERE id = :id'
            );
            $stmt->execute([
                'id'        => $category->getId(),
                'nama'      => $category->getNama(),
                'deskripsi' => $category->getDeskripsi(),
            ]);
        } catch (PDOException $e) {
            $this->rethrowDuplicate($e);
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Menerjemahkan error teknis database menjadi error domain.
     *
     * @throws ConflictException|PDOException
     */
    private function rethrowDuplicate(PDOException $e): void
    {
        if (($e->errorInfo[1] ?? null) === self::ERR_DUPLICATE) {
            throw new ConflictException('Nama kategori sudah digunakan');
        }

        throw $e;
    }

    private function hydrate(array $row): Category
    {
        return new Category(
            (int) $row['id'],
            (string) $row['nama'],
            $row['deskripsi'] !== null ? (string) $row['deskripsi'] : null
        );
    }
}
