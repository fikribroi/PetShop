-- ==============================================================
-- TUGAS BESAR SISTEM BASIS DATA
-- Topik: Sistem Pet Shop & Penitipan Hewan Peliharaan
-- Database: petshop_db
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `petshop_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `petshop_db`;

-- Matikan cek foreign key saat drop tabel
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `penitipan`;
DROP TABLE IF EXISTS `hewan`;
DROP TABLE IF EXISTS `pemilik`;
DROP TABLE IF EXISTS `kandang`;
DROP TABLE IF EXISTS `layanan`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================
-- 1. TABEL USERS (Autentikasi Login Admin / Petugas)
-- ==============================================================
CREATE TABLE `users` (
    `id_user` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `role` ENUM('Admin', 'Petugas') NOT NULL DEFAULT 'Petugas',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================================================
-- 2. TABEL PEMILIK (Master Data Pelanggan / Pemilik Hewan)
-- ==============================================================
CREATE TABLE `pemilik` (
    `id_pemilik` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_pemilik` VARCHAR(100) NOT NULL,
    `no_hp` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) UNIQUE,
    `alamat` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================================================
-- 3. TABEL HEWAN (Data Hewan Peliharaan)
-- Relasi: id_pemilik -> pemilik(id_pemilik) [ON DELETE CASCADE]
-- ==============================================================
CREATE TABLE `hewan` (
    `id_hewan` INT AUTO_INCREMENT PRIMARY KEY,
    `id_pemilik` INT NOT NULL,
    `nama_hewan` VARCHAR(50) NOT NULL,
    `jenis_hewan` ENUM('Kucing', 'Anjing', 'Kelinci', 'Hamster', 'Burung', 'Reptil') NOT NULL,
    `ras` VARCHAR(50) NOT NULL,
    `umur_bulan` INT NOT NULL CHECK (`umur_bulan` >= 0),
    `jenis_kelamin` ENUM('Jantan', 'Betina') NOT NULL,
    `berat_kg` DECIMAL(4,2) NOT NULL CHECK (`berat_kg` > 0),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_hewan_pemilik` FOREIGN KEY (`id_pemilik`) 
        REFERENCES `pemilik` (`id_pemilik`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ==============================================================
-- 4. TABEL KANDANG (Fasilitas Ruang Penitipan Hewan)
-- ==============================================================
CREATE TABLE `kandang` (
    `id_kandang` INT AUTO_INCREMENT PRIMARY KEY,
    `nomor_kandang` VARCHAR(20) NOT NULL UNIQUE,
    `tipe_kandang` ENUM('Standar', 'VIP', 'VVIP') NOT NULL,
    `kapasitas` INT NOT NULL DEFAULT 1 CHECK (`kapasitas` > 0),
    `tarif_per_hari` DECIMAL(10,2) NOT NULL CHECK (`tarif_per_hari` > 0),
    `status_kandang` ENUM('Tersedia', 'Terisi', 'Perbaikan') NOT NULL DEFAULT 'Tersedia',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================================================
-- 5. TABEL LAYANAN (Daftar Layanan & Paket Pet Shop / Penitipan)
-- ==============================================================
CREATE TABLE `layanan` (
    `id_layanan` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_layanan` VARCHAR(100) NOT NULL,
    `kategori` ENUM('Penitipan Saja', 'Grooming & Spa', 'Kesehatan & Vaksin', 'Perawatan Khusus') NOT NULL,
    `tarif_layanan` DECIMAL(10,2) NOT NULL CHECK (`tarif_layanan` >= 0),
    `deskripsi` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================================================
-- 6. TABEL PENITIPAN (Transaksi Utama Penitipan Hewan)
-- Relasi JOIN:
--  - id_hewan   -> hewan(id_hewan)
--  - id_kandang -> kandang(id_kandang)
--  - id_layanan -> layanan(id_layanan)
-- ==============================================================
CREATE TABLE `penitipan` (
    `id_penitipan` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_transaksi` VARCHAR(30) NOT NULL UNIQUE,
    `id_hewan` INT NOT NULL,
    `id_kandang` INT NOT NULL,
    `id_layanan` INT NOT NULL,
    `tgl_masuk` DATE NOT NULL,
    `tgl_keluar` DATE NOT NULL,
    `total_hari` INT NOT NULL CHECK (`total_hari` > 0),
    `total_biaya` DECIMAL(12,2) NOT NULL CHECK (`total_biaya` >= 0),
    `status_penitipan` ENUM('Booking', 'Aktif', 'Selesai', 'Batal') NOT NULL DEFAULT 'Booking',
    `catatan` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_penitipan_hewan` FOREIGN KEY (`id_hewan`) 
        REFERENCES `hewan` (`id_hewan`) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_penitipan_kandang` FOREIGN KEY (`id_kandang`) 
        REFERENCES `kandang` (`id_kandang`) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_penitipan_layanan` FOREIGN KEY (`id_layanan`) 
        REFERENCES `layanan` (`id_layanan`) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ==============================================================
-- DUMMY DATA MINIMAL 10 DATA PER TABEL
-- ==============================================================

-- 1. Data Users (Password hash untuk 'admin123' & 'petugas123', serta md5 fallback)
INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', '$2y$10$wTkyg.fV/a7VzG9VjFzWk.4uO6sA4fW03ZtZk0hOq6ZJj7P50w3z2', 'Administrator Utama', 'Admin'),
(2, 'petugas', '$2y$10$wTkyg.fV/a7VzG9VjFzWk.4uO6sA4fW03ZtZk0hOq6ZJj7P50w3z2', 'Siti Rahmawati', 'Petugas'),
(3, 'budi_pet', '$2y$10$wTkyg.fV/a7VzG9VjFzWk.4uO6sA4fW03ZtZk0hOq6ZJj7P50w3z2', 'Budi Santoso', 'Petugas');
-- Catatan: Password default adalah "admin123"

-- 2. Data Pemilik (10 Data)
INSERT INTO `pemilik` (`id_pemilik`, `nama_pemilik`, `no_hp`, `email`, `alamat`) VALUES
(1, 'Ahmad Fauzi', '081234567801', 'ahmad.fauzi@gmail.com', 'Jl. Merdeka No. 12, Bandung'),
(2, 'Citra Lestari', '081234567802', 'citra.lestari@gmail.com', 'Jl. Riau No. 45, Bandung'),
(3, 'Dedi Kurniawan', '081234567803', 'dedi.kurnia@yahoo.com', 'Jl. Buah Batu No. 88, Bandung'),
(4, 'Eka Wahyuni', '081234567804', 'eka.wahyu@gmail.com', 'Jl. Dago No. 101, Bandung'),
(5, 'Fajar Nugraha', '081234567805', 'fajar.nugraha@gmail.com', 'Jl. Cihampelas No. 23, Bandung'),
(6, 'Gita Permata', '081234567806', 'gita.permata@hotmail.com', 'Jl. Setiabudi No. 15, Bandung'),
(7, 'Hendra Saputra', '081234567807', 'hendra.s@gmail.com', 'Jl. Pasir Kaliki No. 60, Bandung'),
(8, 'Indah Pratiwi', '081234567808', 'indah.pratiwi@gmail.com', 'Jl. Soekarno Hatta No. 210, Bandung'),
(9, 'Joko Susilo', '081234567809', 'joko.susilo@yahoo.com', 'Jl. Antapani No. 34, Bandung'),
(10, 'Kartika Sari', '081234567810', 'kartika.sari@gmail.com', 'Jl. Sukajadi No. 77, Bandung');

-- 3. Data Hewan (10 Data)
INSERT INTO `hewan` (`id_hewan`, `id_pemilik`, `nama_hewan`, `jenis_hewan`, `ras`, `umur_bulan`, `jenis_kelamin`, `berat_kg`) VALUES
(1, 1, 'Milo', 'Kucing', 'Persia Peaknose', 18, 'Jantan', 4.20),
(2, 2, 'Luna', 'Kucing', 'British Shorthair', 24, 'Betina', 3.80),
(3, 3, 'Rocky', 'Anjing', 'Golden Retriever', 36, 'Jantan', 28.50),
(4, 4, 'Choco', 'Anjing', 'Poodle Toy', 12, 'Jantan', 4.50),
(5, 5, 'Snowy', 'Kelinci', 'Holland Lop', 8, 'Betina', 1.80),
(6, 6, 'Kiko', 'Hamster', 'Syrian Longhair', 6, 'Jantan', 0.15),
(7, 7, 'Rio', 'Burung', 'Lovebird Lutino', 14, 'Jantan', 0.05),
(8, 8, 'Bella', 'Kucing', 'Siam', 10, 'Betina', 3.20),
(9, 9, 'Max', 'Anjing', 'Siberian Husky', 30, 'Jantan', 24.00),
(10, 10, 'Oreo', 'Kucing', 'Domestic Shorthair', 15, 'Betina', 3.60);

-- 4. Data Kandang (10 Data)
INSERT INTO `kandang` (`id_kandang`, `nomor_kandang`, `tipe_kandang`, `kapasitas`, `tarif_per_hari`, `status_kandang`) VALUES
(1, 'K-01', 'Standar', 1, 45000.00, 'Terisi'),
(2, 'K-02', 'Standar', 1, 45000.00, 'Terisi'),
(3, 'K-03', 'Standar', 1, 45000.00, 'Tersedia'),
(4, 'K-04', 'Standar', 1, 45000.00, 'Tersedia'),
(5, 'K-05', 'VIP', 1, 75000.00, 'Terisi'),
(6, 'K-06', 'VIP', 1, 75000.00, 'Terisi'),
(7, 'K-07', 'VIP', 1, 75000.00, 'Tersedia'),
(8, 'K-08', 'VVIP', 2, 120000.00, 'Terisi'),
(9, 'K-09', 'VVIP', 2, 120000.00, 'Tersedia'),
(10, 'K-10', 'Standar', 1, 45000.00, 'Perbaikan');

-- 5. Data Layanan (10 Data)
INSERT INTO `layanan` (`id_layanan`, `nama_layanan`, `kategori`, `tarif_layanan`, `deskripsi`) VALUES
(1, 'Paket Penitipan Basic', 'Penitipan Saja', 0.00, 'Fasilitas kandang standar, makan 2x sehari, pembersihan rutin.'),
(2, 'Paket Penitipan Premium Play', 'Penitipan Saja', 25000.00, 'Fasilitas kandang plus area bermain khusus 30 menit per hari.'),
(3, 'Grooming Mandi Sehat Reguler', 'Grooming & Spa', 50000.00, 'Mandi sampo anti jamur & kutu, potong kuku, dan bersihkan telinga.'),
(4, 'Grooming & Spa Aromaterapi', 'Grooming & Spa', 85000.00, 'Mandi lengkap, hair styling, vitamin bulu, dan pijat relaksasi.'),
(5, 'Cukur Bulu & Styling (Grooming Model)', 'Grooming & Spa', 70000.00, 'Pencukuran bulu rapi sesuai model breed/ras hewan.'),
(6, 'Vaksinasi Rabies & Buku Sehat', 'Kesehatan & Vaksin', 100000.00, 'Suntik vaksin rabies resmi dokter hewan bersertifikat.'),
(7, 'Pemberian Obat Cacing & Kutu', 'Kesehatan & Vaksin', 35000.00, 'Pemberian obat cacing oral dan tetes kutu tetes tengkuk.'),
(8, 'Pemeriksaan Kesehatan Dokter Hewan', 'Kesehatan & Vaksin', 60000.00, 'General health checkup oleh dokter hewan in-house.'),
(9, 'Perawatan Khusus Pasca Operasi / Diet', 'Perawatan Khusus', 40000.00, 'Pemberian obat terjadwal, diet khusus sesuai resep medis.'),
(10, 'Latihan Kepatuhan & Sosialisasi', 'Perawatan Khusus', 50000.00, 'Sesi latihan dasar obedience dan sosialisasi dengan trainer.');

-- 6. Data Penitipan (10 Transaksi)
INSERT INTO `penitipan` (`id_penitipan`, `kode_transaksi`, `id_hewan`, `id_kandang`, `id_layanan`, `tgl_masuk`, `tgl_keluar`, `total_hari`, `total_biaya`, `status_penitipan`, `catatan`) VALUES
(1, 'TRX-20261101-001', 1, 1, 3, '2026-11-01', '2026-11-04', 3, 185000.00, 'Aktif', 'Milo suka makanan basah rasa tuna, mandi di hari terakhir.'),
(2, 'TRX-20261101-002', 2, 5, 4, '2026-11-01', '2026-11-05', 4, 385000.00, 'Aktif', 'Luna agak pemalu, sediakan selimut kesayangannya.'),
(3, 'TRX-20261102-003', 3, 8, 1, '2026-11-02', '2026-11-07', 5, 600000.00, 'Aktif', 'Rocky butuh jalan pagi 15 menit di halaman.'),
(4, 'TRX-20261102-004', 4, 6, 3, '2026-11-02', '2026-11-04', 2, 200000.00, 'Aktif', 'Choco alergi ayam, tolong beri pakan lamb & rice.'),
(5, 'TRX-20261025-005', 5, 2, 1, '2026-10-25', '2026-10-28', 3, 135000.00, 'Selesai', 'Kelinci sehat, makan hay timothy lancar.'),
(6, 'TRX-20261026-006', 6, 3, 1, '2026-10-26', '2026-10-29', 3, 135000.00, 'Selesai', 'Hamster aktif roda putar terpasang.'),
(7, 'TRX-20261028-007', 7, 4, 8, '2026-10-28', '2026-10-31', 3, 195000.00, 'Selesai', 'Pemeriksaan paruh dan sayap normal.'),
(8, 'TRX-20261103-008', 8, 7, 7, '2026-11-05', '2026-11-08', 3, 260000.00, 'Booking', 'Booking libur akhir pekan depan, obat kutu saat masuk.'),
(9, 'TRX-20261103-009', 9, 9, 2, '2026-11-06', '2026-11-10', 4, 505000.00, 'Booking', 'Booking liburan keluarga, kamar VVIP.'),
(10, 'TRX-20261020-010', 10, 1, 1, '2026-10-20', '2026-10-21', 1, 45000.00, 'Batal', 'Dibatalkan oleh pemilik karena rencana perjalanan batal.');

-- ==============================================================
-- CONTOH IMPLEMENTASI QUERY JOIN (Sesuai Soal Poin 4)
-- ==============================================================
-- Query 1: INNER JOIN Multi-tabel untuk menampilkan detail transaksi penitipan lengkap
-- SELECT 
--     p.kode_transaksi,
--     pm.nama_pemilik,
--     pm.no_hp,
--     h.nama_hewan,
--     h.jenis_hewan,
--     h.ras,
--     k.nomor_kandang,
--     k.tipe_kandang,
--     l.nama_layanan,
--     p.tgl_masuk,
--     p.tgl_keluar,
--     p.total_hari,
--     p.total_biaya,
--     p.status_penitipan
-- FROM penitipan p
-- INNER JOIN hewan h ON p.id_hewan = h.id_hewan
-- INNER JOIN pemilik pm ON h.id_pemilik = pm.id_pemilik
-- INNER JOIN kandang k ON p.id_kandang = k.id_kandang
-- INNER JOIN layanan l ON p.id_layanan = l.id_layanan;

