-- ============================================================
-- Skema database: Perpustakaan (LKS PBO - UNTAN)
-- Jalankan: mysql -u root -p < database/schema.sql
-- Aman dijalankan berulang kali (idempotent).
-- ============================================================

CREATE DATABASE IF NOT EXISTS perpustakaan
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE perpustakaan;

-- ------------------------------------------------------------
-- Tabel books (Langkah 3)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS books (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    judul      VARCHAR(255) NOT NULL,
    penulis    VARCHAR(255) NOT NULL,
    tahun      INT NOT NULL,
    stok       INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO books (judul, penulis, tahun, stok)
SELECT 'Laskar Pelangi', 'Andrea Hirata', 2005, 5
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM books WHERE judul = 'Laskar Pelangi');

INSERT INTO books (judul, penulis, tahun, stok)
SELECT 'Bumi Manusia', 'Pramoedya Ananta Toer', 1980, 3
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM books WHERE judul = 'Bumi Manusia');

-- ------------------------------------------------------------
-- Tabel categories (Langkah 13)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100) NOT NULL UNIQUE,
    deskripsi  TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (nama, deskripsi) VALUES
    ('Fiksi', 'Buku-buku fiksi dan novel'),
    ('Non-Fiksi', 'Buku non-fiksi, sains, sejarah');
