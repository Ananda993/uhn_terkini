-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 08, 2026 at 02:11 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uhn-terkini`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `informasi_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookmarks`
--

INSERT INTO `bookmarks` (`id`, `user_id`, `informasi_id`, `created_at`) VALUES
(1, 2, 24, '2026-05-12 07:17:19');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-folder',
  `warna` varchar(7) DEFAULT '#6366f1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `nama`, `slug`, `icon`, `warna`, `created_at`) VALUES
(1, 'Beasiswa ggu', 'beasiswa-ggu', 'fas fa-graduation-cap', '#6366f1', '2026-04-27 12:11:58'),
(2, 'Lomba', 'lomba', 'fas fa-trophy', '#f59e0b', '2026-04-27 12:11:58'),
(3, 'Seminar', 'seminar', 'fas fa-chalkboard-teacher', '#10b981', '2026-04-27 12:11:58'),
(4, 'Kegiatan Kampus', 'kegiatan-kampus', 'fas fa-university', '#ef4444', '2026-04-27 12:11:58'),
(5, 'Akademik', 'akademik', 'fas fa-book', '#3b82f6', '2026-04-27 12:11:58');

-- --------------------------------------------------------

--
-- Table structure for table `fakultas`
--

CREATE TABLE `fakultas` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `fakultas`
--

INSERT INTO `fakultas` (`id`, `nama`, `slug`, `is_active`, `created_at`) VALUES
(1, 'FDA', 'fda', 1, '2026-05-26 01:18:45'),
(2, 'FDD', 'fdd', 1, '2026-05-26 01:18:45'),
(3, 'FBW', 'fbw', 1, '2026-05-26 01:18:45'),
(4, 'FAST', 'fast', 1, '2026-05-26 01:18:45'),
(5, 'Kedokteran', 'kedokteran', 1, '2026-05-26 01:18:45');

-- --------------------------------------------------------

--
-- Table structure for table `informasi`
--

CREATE TABLE `informasi` (
  `id` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sumber` varchar(100) DEFAULT NULL,
  `deskripsi` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `is_urgent` tinyint(1) DEFAULT '0',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `views` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `informasi`
--

INSERT INTO `informasi` (`id`, `judul`, `slug`, `sumber`, `deskripsi`, `gambar`, `deadline`, `category_id`, `user_id`, `is_urgent`, `status`, `views`, `created_at`, `updated_at`) VALUES
(24, 'Semarakkan Dies Natalis ke-70, UHN IGB Sugriwa Denpasar Gelar Aneka Lomba untuk Mahasiswa', 'semarakkan-dies-natalis-ke-70-uhn-igb-sugriwa-denpasar-gelar-aneka-lomba-untuk-mahasiswa', NULL, '<p style=\"text-align: left;\"><strong>Bangli</strong> - Dalam rangka memperingati Hari Ulang Tahun ke-70, Universitas Hindu Negeri I Gusti Bagus Sugriwa Denpasar (UHN IGB Sugriwa) menggelar serangkaian lomba yang diikuti dengan antusias oleh para mahasiswa. Kegiatan yang berlangsung sejak awal bulan ini bertujuan untuk mempererat tali persaudaraan dan mengasah kreativitas mahasiswa di tengah perkuliahan.</p><p>Rangkaian lomba yang diadakan meliputi berbagai cabang, mulai dari bidang akademik seperti Debat Bahasa Inggris dan Karya Tulis Ilmiah, hingga bidang seni dan olahraga seperti Paduan Suara, Cipta Baca Puisi, Bulu Tangkis, serta E-Sports. \"Kami ingin Dies Natalis kali ini tidak hanya menjadi seremonial, tetapi juga wadah bagi mahasiswa untuk menunjukkan bakat terbaik mereka,\" ujar salah satu panitia di sela-sela acara.</p><p>Puncak perlombaan dan seluruh rangkaian Dies Natalis rencananya akan ditutup dengan acara puncak yang digelar di kampus utama Universitas Hindu Negeri I Gusti Bagus Sugriwa Denpasar. Masyarakat dan civitas academica diundang untuk menyaksikan final lomba serta berbagai pertunjukan seni budaya. Semangat kebersamaan dan sportivitas sangat terasa di setiap sudut kampus selama perhelatan ini berlangsung.</p>', 'info_1778567213_2c4fc0cf.png', '2026-05-20', 2, 1, 0, 'approved', 8, '2026-05-12 06:26:53', '2026-05-26 00:26:47'),
(25, 'www', 'www', NULL, '<p>jndjedbkebjkewdbekjwjdejdedkdedhgdewdgewidewdweg</p>', NULL, '2026-05-27', 5, 1, 0, 'approved', 1, '2026-05-26 00:35:05', '2026-05-26 01:30:23');

-- --------------------------------------------------------

--
-- Table structure for table `ormawa`
--

CREATE TABLE `ormawa` (
  `id` int NOT NULL,
  `nama` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `scope` enum('univ','fakultas') DEFAULT 'fakultas',
  `fakultas_id` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ormawa`
--

INSERT INTO `ormawa` (`id`, `nama`, `slug`, `scope`, `fakultas_id`, `is_active`, `created_at`) VALUES
(1, 'BEM UNIV', 'bem-univ', 'univ', NULL, 1, '2026-05-26 01:18:45'),
(2, 'DPM UNIV', 'dpm-univ', 'univ', NULL, 1, '2026-05-26 01:18:45'),
(3, 'BPM FDD', 'bpm-fdd', 'fakultas', 2, 1, '2026-05-26 01:18:45'),
(4, 'BEM FDD', 'bem-fdd', 'fakultas', 2, 1, '2026-05-26 01:18:45'),
(5, 'HMJ HUKUM', 'hmj-hukum', 'fakultas', 2, 1, '2026-05-26 01:18:45'),
(6, 'HMJ KWU', 'hmj-kwu', 'fakultas', 2, 1, '2026-05-26 01:18:45'),
(7, 'HMJ INFORMATIKA', 'hmj-informatika', 'fakultas', 2, 1, '2026-05-26 01:18:45'),
(8, 'HMJ PARBUD', 'hmj-parbud', 'fakultas', 2, 1, '2026-05-26 01:18:45'),
(9, 'BPM FDA', 'bpm-fda', 'fakultas', 1, 1, '2026-05-26 01:18:45'),
(10, 'BEM FDA', 'bem-fda', 'fakultas', 1, 1, '2026-05-26 01:18:45'),
(11, 'HMJ PGSD', 'hmj-pgsd', 'fakultas', 1, 1, '2026-05-26 01:18:45'),
(12, 'BPM FBW', 'bpm-fbw', 'fakultas', 3, 1, '2026-05-26 01:18:45'),
(13, 'BEM FBW', 'bem-fbw', 'fakultas', 3, 1, '2026-05-26 01:18:45');

-- --------------------------------------------------------

--
-- Table structure for table `ukm`
--

CREATE TABLE `ukm` (
  `id` int NOT NULL,
  `nama` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ukm`
--

INSERT INTO `ukm` (`id`, `nama`, `slug`, `is_active`, `created_at`) VALUES
(1, 'UKM Seni', 'ukm-seni', 1, '2026-05-26 01:21:48'),
(2, 'UKM Olahraga', 'ukm-olahraga', 1, '2026-05-26 01:21:48'),
(3, 'UKM Kewirausahaan', 'ukm-kewirausahaan', 1, '2026-05-26 01:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','publisher','mahasiswa') DEFAULT 'mahasiswa',
  `no_hp` varchar(20) DEFAULT NULL,
  `fakultas` varchar(50) DEFAULT NULL,
  `ormawa` varchar(100) DEFAULT NULL,
  `ukm_id` int DEFAULT NULL,
  `periode` varchar(50) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `no_hp`, `fakultas`, `ormawa`, `ukm_id`, `periode`, `foto_profil`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@uhn.ac.id', '$2y$10$zS93f11c3gvmFgrhGJ92Iey39nIRkN.XVVSqrbN8ThJyNfiAOHnb6', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-27 12:11:58', '2026-04-27 12:11:58'),
(2, 'gusnan', 'gusnan@uhn.id', '$2y$10$ZeR6pnlU2K0xW5aEsUE8rO9DMrMMTYYh/PPxT6zU3WsP2BTWrfVSW', 'publisher', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-05 00:50:32', '2026-05-05 00:50:32'),
(3, 'adi', 'adiganteng@uhn.ac.id', '$2y$10$GVwiRdwTrc3wbKFITpnN.OQGWaLD4BqyJIYYE9ze61cRPKx8fUJRG', 'mahasiswa', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-05 01:30:19', '2026-05-05 01:30:19'),
(4, 'hanggara', 'anggara@gmail.com', '$2y$10$6phZtk2K2eYQeTPO.bytG.CN.g0cSNWFX5RlGWySCBqCeF.CVnAny', 'mahasiswa', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-26 00:48:04', '2026-05-26 00:48:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bookmark` (`user_id`,`informasi_id`),
  ADD KEY `informasi_id` (`informasi_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `fakultas`
--
ALTER TABLE `fakultas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `informasi`
--
ALTER TABLE `informasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ormawa`
--
ALTER TABLE `ormawa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fakultas_id` (`fakultas_id`);

--
-- Indexes for table `ukm`
--
ALTER TABLE `ukm`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fakultas`
--
ALTER TABLE `fakultas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `informasi`
--
ALTER TABLE `informasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `ormawa`
--
ALTER TABLE `ormawa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `ukm`
--
ALTER TABLE `ukm`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`informasi_id`) REFERENCES `informasi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `informasi`
--
ALTER TABLE `informasi`
  ADD CONSTRAINT `informasi_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `informasi_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ormawa`
--
ALTER TABLE `ormawa`
  ADD CONSTRAINT `ormawa_ibfk_1` FOREIGN KEY (`fakultas_id`) REFERENCES `fakultas` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
