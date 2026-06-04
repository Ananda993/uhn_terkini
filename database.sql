-- =========================================
-- UHN Terkini - Database SQL
-- Portal Informasi Kampus Terpusat
-- =========================================

-- Buat database
CREATE DATABASE IF NOT EXISTS `uhn-terkini` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `uhn-terkini`;

-- =========================================
-- TABEL USERS
-- =========================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin','publisher','mahasiswa') DEFAULT 'mahasiswa',
    `no_hp` VARCHAR(20) DEFAULT NULL,
    `fakultas` VARCHAR(50) DEFAULT NULL,
    `ormawa` VARCHAR(100) DEFAULT NULL,
    `ukm_id` INT DEFAULT NULL,
    `periode` VARCHAR(50) DEFAULT NULL,
    `foto_profil` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- TABEL CATEGORIES
-- =========================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) UNIQUE NOT NULL,
    `icon` VARCHAR(50) DEFAULT 'fas fa-folder',
    `warna` VARCHAR(7) DEFAULT '#6366f1',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- TABEL FAKULTAS
-- =========================================
CREATE TABLE IF NOT EXISTS `fakultas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) UNIQUE NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- TABEL ORMAWA
-- =========================================
CREATE TABLE IF NOT EXISTS `ormawa` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(120) NOT NULL,
    `slug` VARCHAR(120) UNIQUE NOT NULL,
    `scope` ENUM('univ','fakultas') DEFAULT 'fakultas',
    `fakultas_id` INT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`fakultas_id`) REFERENCES `fakultas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- TABEL UKM
-- =========================================
CREATE TABLE IF NOT EXISTS `ukm` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(120) NOT NULL,
    `slug` VARCHAR(120) UNIQUE NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- TABEL INFORMASI
-- =========================================
CREATE TABLE IF NOT EXISTS `informasi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `judul` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `sumber` VARCHAR(100) DEFAULT NULL,
    `deskripsi` TEXT NOT NULL,
    `gambar` VARCHAR(255) DEFAULT NULL,
    `deadline` DATE DEFAULT NULL,
    `category_id` INT,
    `user_id` INT,
    `is_urgent` TINYINT(1) DEFAULT 0,
    `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
    `views` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- TABEL BOOKMARKS
-- =========================================
CREATE TABLE IF NOT EXISTS `bookmarks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `informasi_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_bookmark` (`user_id`, `informasi_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`informasi_id`) REFERENCES `informasi`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- SEED: Admin Default
-- Password: admin123
-- =========================================
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `is_active`) VALUES
('Administrator', 'admin@uhn.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- =========================================
-- SEED: Categories
-- =========================================
INSERT INTO `categories` (`nama`, `slug`, `icon`, `warna`) VALUES
('Beasiswa', 'beasiswa', 'fas fa-graduation-cap', '#6366f1'),
('Lomba', 'lomba', 'fas fa-trophy', '#f59e0b'),
('Seminar', 'seminar', 'fas fa-chalkboard-teacher', '#10b981'),
('Kegiatan Kampus', 'kegiatan-kampus', 'fas fa-university', '#ef4444'),
('Akademik', 'akademik', 'fas fa-book', '#3b82f6'),
('Organisasi', 'organisasi', 'fas fa-users', '#8b5cf6');

-- =========================================
-- SEED: Fakultas
-- =========================================
INSERT INTO `fakultas` (`nama`, `slug`, `is_active`) VALUES
('FDA', 'fda', 1),
('FDD', 'fdd', 1),
('FBW', 'fbw', 1),
('FAST', 'fast', 1),
('Kedokteran', 'kedokteran', 1);

-- =========================================
-- SEED: ORMAWA
-- =========================================
INSERT INTO `ormawa` (`nama`, `slug`, `scope`, `fakultas_id`, `is_active`) VALUES
('BEM UNIV', 'bem-univ', 'univ', NULL, 1),
('DPM UNIV', 'dpm-univ', 'univ', NULL, 1),
('BPM FDD', 'bpm-fdd', 'fakultas', (SELECT id FROM fakultas WHERE nama='FDD' LIMIT 1), 1),
('BEM FDD', 'bem-fdd', 'fakultas', (SELECT id FROM fakultas WHERE nama='FDD' LIMIT 1), 1),
('HMJ HUKUM', 'hmj-hukum', 'fakultas', (SELECT id FROM fakultas WHERE nama='FDD' LIMIT 1), 1),
('HMJ KWU', 'hmj-kwu', 'fakultas', (SELECT id FROM fakultas WHERE nama='FDD' LIMIT 1), 1),
('HMJ INFORMATIKA', 'hmj-informatika', 'fakultas', (SELECT id FROM fakultas WHERE nama='FDD' LIMIT 1), 1),
('HMJ PARBUD', 'hmj-parbud', 'fakultas', (SELECT id FROM fakultas WHERE nama='FDD' LIMIT 1), 1),
('BPM FDA', 'bpm-fda', 'fakultas', (SELECT id FROM fakultas WHERE nama='FDA' LIMIT 1), 1),
('BEM FDA', 'bem-fda', 'fakultas', (SELECT id FROM fakultas WHERE nama='FDA' LIMIT 1), 1),
('HMJ PGSD', 'hmj-pgsd', 'fakultas', (SELECT id FROM fakultas WHERE nama='FDA' LIMIT 1), 1),
('BPM FBW', 'bpm-fbw', 'fakultas', (SELECT id FROM fakultas WHERE nama='FBW' LIMIT 1), 1),
('BEM FBW', 'bem-fbw', 'fakultas', (SELECT id FROM fakultas WHERE nama='FBW' LIMIT 1), 1);

-- =========================================
-- SEED: UKM
-- =========================================
INSERT INTO `ukm` (`nama`, `slug`, `is_active`) VALUES
('UKM Seni', 'ukm-seni', 1),
('UKM Olahraga', 'ukm-olahraga', 1),
('UKM Kewirausahaan', 'ukm-kewirausahaan', 1);

-- =========================================
-- SEED: Sample Informasi
-- =========================================
INSERT INTO `informasi` (`judul`, `slug`, `deskripsi`, `deadline`, `category_id`, `user_id`, `status`, `views`) VALUES
('Beasiswa Bank Indonesia 2026', 'beasiswa-bank-indonesia-2026', 'Pendaftaran Beasiswa Bank Indonesia untuk mahasiswa berprestasi semester genap 2025/2026. Beasiswa ini mencakup biaya kuliah penuh dan tunjangan hidup bulanan.', '2026-06-15', 1, 1, 'approved', 1250),
('Lomba Debat Nasional Piala Rektor', 'lomba-debat-nasional-piala-rektor', 'Kompetisi debat nasional untuk seluruh mahasiswa se-Indonesia. Hadiah total Rp 50 juta dan piala bergilir Rektor UHN.', '2026-05-20', 2, 1, 'approved', 980),
('Seminar Teknologi AI & Machine Learning', 'seminar-teknologi-ai-ml', 'Seminar nasional dengan pembicara dari Google dan Microsoft membahas perkembangan AI dan peluang karir di bidang teknologi.', '2026-05-10', 3, 1, 'approved', 2100),
('Workshop UI/UX Design Bootcamp', 'workshop-uiux-design-bootcamp', 'Bootcamp intensif 3 hari tentang UI/UX Design menggunakan Figma. Sertifikat dan portofolio langsung!', '2026-05-25', 5, 1, 'approved', 750),
('Pendaftaran UKM Semester Genap', 'pendaftaran-ukm-semester-genap', 'Dibuka pendaftaran anggota baru Unit Kegiatan Mahasiswa untuk semester genap. Tersedia 15+ UKM pilihan.', '2026-05-05', 6, 1, 'approved', 1800),
('Dies Natalis UHN ke-70', 'dies-natalis-uhn-ke-70', 'Rangkaian acara perayaan Dies Natalis UHN ke-70 dengan berbagai lomba, pameran, dan konser musik.', '2026-06-01', 4, 1, 'approved', 3200),
('Beasiswa Djarum Foundation 2026', 'beasiswa-djarum-foundation-2026', 'Program beasiswa Djarum Plus untuk mahasiswa semester 4-6 dengan IPK minimal 3.2. Termasuk soft skill training.', '2026-05-30', 1, 1, 'approved', 1500),
('Kompetisi Hackathon UHN Tech Fest', 'kompetisi-hackathon-uhn-tech-fest', 'Hackathon 24 jam membangun solusi digital untuk masalah kampus. Tim 3-5 orang, hadiah total Rp 30 juta.', '2026-05-18', 2, 1, 'approved', 890);
