-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2024 at 04:46 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sipadu`
--

-- --------------------------------------------------------

--
-- Table structure for table `keluarga`
--

CREATE TABLE `keluarga` (
  `id_keluarga` int(11) NOT NULL,
  `no_kk` varchar(16) NOT NULL,
  `kepala_keluarga` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `keluarga`
--

INSERT INTO `keluarga` (`id_keluarga`, `no_kk`, `kepala_keluarga`, `alamat`, `created_at`, `updated_at`) VALUES
(1, '5425136273823456', 'JOS BRO', 'sukaraya', '2024-12-14 03:52:57', '2024-12-14 03:52:57'),
(2, '3434345454523432', 'Boss nyaaa', 'Mulyoharjo', '2024-12-14 18:59:04', '2024-12-14 18:59:04');

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `jenis_laporan` varchar(50) NOT NULL,
  `tanggal_laporan` date NOT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `aktivitas` enum('TAMBAH','EDIT','HAPUS') NOT NULL,
  `keterangan` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id_log`, `user_id`, `aktivitas`, `keterangan`, `created_at`) VALUES
(1, 9, 'EDIT', 'Mengubah data penduduk: Apa aja ya (NIK: 3234234234532453)', '2024-12-14 21:28:02'),
(2, 9, 'EDIT', 'Mengubah data penduduk: Apa aja ya (NIK: 3234234234532453)', '2024-12-14 21:28:08'),
(3, 9, 'HAPUS', 'Logout dari sistem', '2024-12-14 21:32:28'),
(4, 9, 'TAMBAH', 'Login ke sistem', '2024-12-14 21:32:30'),
(5, 9, 'HAPUS', 'Logout dari sistem', '2024-12-14 21:32:47'),
(6, 9, 'TAMBAH', 'Login ke sistem', '2024-12-15 04:49:11'),
(7, 9, 'TAMBAH', 'Menambah data penduduk: Halo saya siapa (NIK: 3436352626536362)', '2024-12-15 04:51:15'),
(8, 9, 'HAPUS', 'Logout dari sistem', '2024-12-15 05:05:15'),
(9, 9, 'TAMBAH', 'Login ke sistem', '2024-12-15 05:05:17'),
(10, 9, 'HAPUS', 'Menghapus peristiwa DATANG untuk penduduk: KJNKJNKVDK (NIK: 6657575767868768)', '2024-12-15 05:32:14'),
(11, 9, 'HAPUS', 'Menghapus peristiwa KELAHIRAN untuk penduduk: Apa aja ya (NIK: 3234234234532453)', '2024-12-15 05:32:18'),
(12, 9, 'HAPUS', 'Menghapus peristiwa PINDAH untuk penduduk: Apa aja ya (NIK: 3234234234532453)', '2024-12-15 05:32:21'),
(13, 9, 'HAPUS', 'Menghapus peristiwa PINDAH untuk penduduk: KJNKJNKVDK (NIK: 6657575767868768)', '2024-12-15 05:36:43'),
(14, 9, 'HAPUS', 'Menghapus peristiwa DATANG untuk penduduk: KJNKJNKVDK (NIK: 6657575767868768)', '2024-12-15 05:41:52'),
(15, 9, 'HAPUS', 'Menghapus peristiwa KEMATIAN untuk penduduk: Halo saya siapa (NIK: 3436352626536362)', '2024-12-15 05:41:55'),
(16, 9, 'HAPUS', 'Menghapus peristiwa KELAHIRAN untuk penduduk: Apa aja ya (NIK: 3234234234532453)', '2024-12-15 05:41:57'),
(17, 9, 'HAPUS', 'Menghapus peristiwa PINDAH untuk penduduk: KJNKJNKVDK (NIK: 6657575767868768)', '2024-12-15 05:44:37'),
(18, 9, 'HAPUS', 'Menghapus peristiwa KELAHIRAN untuk penduduk: Apa aja ya (NIK: 3234234234532453)', '2024-12-15 05:44:39'),
(19, 9, 'HAPUS', 'Menghapus peristiwa KEMATIAN untuk penduduk: Halo saya siapa (NIK: 3436352626536362)', '2024-12-15 05:44:42'),
(20, 9, 'HAPUS', 'Menghapus peristiwa PINDAH untuk penduduk: KJNKJNKVDK (NIK: 6657575767868768)', '2024-12-15 05:56:19'),
(21, 9, 'HAPUS', 'Logout dari sistem', '2024-12-15 06:10:15'),
(22, 9, 'TAMBAH', 'Login ke sistem', '2024-12-15 10:15:55'),
(23, 9, 'HAPUS', 'Logout dari sistem', '2024-12-15 10:25:20'),
(24, 9, 'TAMBAH', 'Login ke sistem', '2024-12-16 03:11:53'),
(25, 9, 'HAPUS', 'Logout dari sistem', '2024-12-16 03:12:06'),
(26, 9, 'TAMBAH', 'Login ke sistem', '2024-12-16 03:12:08'),
(27, 9, 'HAPUS', 'Logout dari sistem', '2024-12-16 03:13:19'),
(28, 9, 'TAMBAH', 'Login ke sistem', '2024-12-16 11:24:19'),
(29, 9, 'TAMBAH', 'Login ke sistem', '2024-12-18 03:15:56'),
(30, 9, 'HAPUS', 'Logout dari sistem', '2024-12-18 03:33:28'),
(31, 9, 'TAMBAH', 'Login ke sistem', '2024-12-18 03:33:48'),
(32, 9, 'HAPUS', 'Logout dari sistem', '2024-12-18 03:33:50'),
(33, 11, 'TAMBAH', 'Login ke sistem', '2024-12-18 03:42:58'),
(34, 11, 'HAPUS', 'Logout dari sistem', '2024-12-18 03:43:01'),
(35, 11, 'TAMBAH', 'Login ke sistem', '2024-12-18 03:43:22'),
(36, 11, 'HAPUS', 'Logout dari sistem', '2024-12-18 03:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `maps`
--

CREATE TABLE `maps` (
  `id_maps` int(11) NOT NULL,
  `id_penduduk` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maps`
--

INSERT INTO `maps` (`id_maps`, `id_penduduk`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(5, 6, -6.58877920, 110.66957660, '2024-12-14 19:00:23', '2024-12-14 19:03:51'),
(6, 5, -6.59249530, 110.66548860, '2024-12-14 19:03:27', '2024-12-14 19:03:27');

-- --------------------------------------------------------

--
-- Table structure for table `penduduk`
--

CREATE TABLE `penduduk` (
  `id_penduduk` int(11) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `status_perkawinan` enum('BELUM MENIKAH','MENIKAH','CERAI HIDUP','CERAI MATI') NOT NULL,
  `agama` varchar(20) NOT NULL,
  `pekerjaan` varchar(50) NOT NULL,
  `id_keluarga` int(11) NOT NULL,
  `alamat` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penduduk`
--

INSERT INTO `penduduk` (`id_penduduk`, `nik`, `nama`, `jenis_kelamin`, `tanggal_lahir`, `status_perkawinan`, `agama`, `pekerjaan`, `id_keluarga`, `alamat`, `created_at`, `updated_at`) VALUES
(5, '6657575767868768', 'KJNKJNKVDK', 'P', '2024-12-27', 'BELUM MENIKAH', 'ISLAM', 'tbtyntynyt', 1, 'ffrtbhntynty', '2024-12-14 05:09:25', '2024-12-14 05:09:25'),
(6, '3234234234532453', 'Apa aja ya', 'L', '2013-01-09', 'BELUM MENIKAH', 'ISLAM', 'Pengusaha', 1, 'Jepara, Panggang', '2024-12-14 18:57:05', '2024-12-14 21:28:08'),
(7, '3436352626536362', 'Halo saya siapa', 'L', '2015-01-15', 'MENIKAH', 'ISLAM', 'pengacara', 1, 'TAHUNAN JEPARA', '2024-12-15 04:51:15', '2024-12-15 04:51:15');

-- --------------------------------------------------------

--
-- Table structure for table `peristiwa`
--

CREATE TABLE `peristiwa` (
  `id_peristiwa` int(11) NOT NULL,
  `id_penduduk` int(11) NOT NULL,
  `jenis_peristiwa` enum('KELAHIRAN','KEMATIAN','PINDAH','DATANG') NOT NULL,
  `tanggal_peristiwa` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peristiwa`
--

INSERT INTO `peristiwa` (`id_peristiwa`, `id_penduduk`, `jenis_peristiwa`, `tanggal_peristiwa`, `keterangan`, `created_at`, `updated_at`) VALUES
(21, 6, 'KEMATIAN', '2024-12-06', 'Meninggal', '2024-12-15 05:49:16', '2024-12-15 05:49:16'),
(22, 7, 'KELAHIRAN', '2024-12-03', 'Melahirkan anak perempuan', '2024-12-15 05:50:50', '2024-12-15 05:50:50'),
(24, 5, 'KELAHIRAN', '2024-12-13', 'Melahirkan anak laki laki', '2024-12-15 05:57:09', '2024-12-15 05:57:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','operator') NOT NULL DEFAULT 'operator',
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`, `email`, `created_at`, `updated_at`) VALUES
(9, 'admin', '$2y$10$a2HMSavaUL2.auseu4FneOYoIBsXAUXYDY5CDTgdmcM/PLyEyd16q', 'Administrator', 'admin', 'admin@sipadu.com', '2024-12-14 02:46:53', '2024-12-14 20:39:56'),
(11, 'admin kita', '$2y$10$aMqlZN.J5TTXSi8VygCW9uAS0gqpmO6DNvoOG4qyOBhHAGKbXK3Lq', 'Admin', 'operator', NULL, '2024-12-18 03:42:51', '2024-12-18 03:42:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `keluarga`
--
ALTER TABLE `keluarga`
  ADD PRIMARY KEY (`id_keluarga`),
  ADD UNIQUE KEY `no_kk` (`no_kk`),
  ADD KEY `idx_no_kk` (`no_kk`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `maps`
--
ALTER TABLE `maps`
  ADD PRIMARY KEY (`id_maps`),
  ADD UNIQUE KEY `id_penduduk` (`id_penduduk`);

--
-- Indexes for table `penduduk`
--
ALTER TABLE `penduduk`
  ADD PRIMARY KEY (`id_penduduk`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `idx_nik` (`nik`),
  ADD KEY `idx_id_keluarga` (`id_keluarga`);

--
-- Indexes for table `peristiwa`
--
ALTER TABLE `peristiwa`
  ADD PRIMARY KEY (`id_peristiwa`),
  ADD KEY `idx_id_penduduk` (`id_penduduk`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `keluarga`
--
ALTER TABLE `keluarga`
  MODIFY `id_keluarga` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `maps`
--
ALTER TABLE `maps`
  MODIFY `id_maps` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `penduduk`
--
ALTER TABLE `penduduk`
  MODIFY `id_penduduk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `peristiwa`
--
ALTER TABLE `peristiwa`
  MODIFY `id_peristiwa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `maps`
--
ALTER TABLE `maps`
  ADD CONSTRAINT `maps_ibfk_1` FOREIGN KEY (`id_penduduk`) REFERENCES `penduduk` (`id_penduduk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `penduduk`
--
ALTER TABLE `penduduk`
  ADD CONSTRAINT `penduduk_ibfk_1` FOREIGN KEY (`id_keluarga`) REFERENCES `keluarga` (`id_keluarga`) ON DELETE CASCADE;

--
-- Constraints for table `peristiwa`
--
ALTER TABLE `peristiwa`
  ADD CONSTRAINT `peristiwa_ibfk_1` FOREIGN KEY (`id_penduduk`) REFERENCES `penduduk` (`id_penduduk`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
