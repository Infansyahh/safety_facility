-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2026 at 04:50 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `safety_facility`
--

-- --------------------------------------------------------

--
-- Table structure for table `agenda_inspeksi`
--

CREATE TABLE `agenda_inspeksi` (
  `id_agenda` int(11) NOT NULL,
  `jenis_inspeksi` varchar(50) NOT NULL,
  `line_area` varchar(50) NOT NULL,
  `id_lampu` varchar(20) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tanggal_jadwal` date NOT NULL,
  `jam_jadwal` time DEFAULT NULL,
  `status` enum('Terjadwal','Berlangsung','Selesai','Terlewat') NOT NULL DEFAULT 'Terjadwal',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `area_line`
--

CREATE TABLE `area_line` (
  `id_line` int(11) NOT NULL,
  `nama_line` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `jenis` varchar(30) NOT NULL DEFAULT 'lampu_emergency'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `area_line`
--

INSERT INTO `area_line` (`id_line`, `nama_line`, `created_at`, `jenis`) VALUES
(2, 'Factory A', '2026-07-02 00:42:28', 'lampu_emergency'),
(3, 'FA', '2026-07-06 03:39:17', 'lampu_exit'),
(4, 'FA', '2026-07-06 07:16:59', 'p3k');

-- --------------------------------------------------------

--
-- Table structure for table `departemen`
--

CREATE TABLE `departemen` (
  `id_departemen` int(11) NOT NULL,
  `nama_departemen` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspeksi_eyewash`
--

CREATE TABLE `inspeksi_eyewash` (
  `id_inspeksi` int(11) NOT NULL,
  `code_eyewash` varchar(50) NOT NULL,
  `tanggal_inspeksi` date NOT NULL,
  `kondisi` enum('baik','rusak','','') NOT NULL,
  `catatan` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspeksi_lampu`
--

CREATE TABLE `inspeksi_lampu` (
  `id_inspeksi` int(11) NOT NULL,
  `code_lampu` varchar(50) NOT NULL,
  `tanggal_inspeksi` date NOT NULL,
  `kondisi` enum('baik','rusak','','') NOT NULL,
  `catatan` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inspeksi_lampu`
--

INSERT INTO `inspeksi_lampu` (`id_inspeksi`, `code_lampu`, `tanggal_inspeksi`, `kondisi`, `catatan`, `username`) VALUES
(5, 'LPE01', '2026-06-30', 'baik', 'Mantap', 'Arip'),
(14, 'LPE01', '2026-07-03', 'baik', 'Mantap', 'Arip'),
(16, 'LE01', '2026-07-06', 'baik', 'BAIK', 'Widiyantoro');

-- --------------------------------------------------------

--
-- Table structure for table `inspeksi_lampu_exit`
--

CREATE TABLE `inspeksi_lampu_exit` (
  `id_inspeksi` int(11) NOT NULL,
  `id_lampu` varchar(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  `tanggal_cek` datetime NOT NULL,
  `kondisi_fisik` enum('Baik','Tidak') NOT NULL DEFAULT 'Baik',
  `kondisi_lampu` enum('Baik','Tidak') NOT NULL DEFAULT 'Baik',
  `kondisi_tulisan` enum('Baik','Tidak') NOT NULL DEFAULT 'Baik',
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspeksi_p3k`
--

CREATE TABLE `inspeksi_p3k` (
  `id_inspeksi` int(11) NOT NULL,
  `code_p3k` varchar(50) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `line_area` varchar(100) DEFAULT NULL,
  `kondisi_kotak` enum('Baik','Tidak') NOT NULL DEFAULT 'Baik',
  `kelengkapan_isi` enum('Lengkap','Tidak') NOT NULL DEFAULT 'Lengkap',
  `expired_obat` enum('Lengkap','Tidak') NOT NULL DEFAULT 'Lengkap',
  `keterangan` text DEFAULT NULL,
  `tanggal_inspeksi` date NOT NULL,
  `kondisi` enum('baik','rusak','','') NOT NULL,
  `catatan` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_eyewash`
--

CREATE TABLE `master_eyewash` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `kondisi` enum('baik','rusak','','') NOT NULL,
  `catatan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_lampu`
--

CREATE TABLE `master_lampu` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `line_area` varchar(100) DEFAULT NULL,
  `lokasi` varchar(255) NOT NULL,
  `indikator_mati_menyala` varchar(20) DEFAULT NULL,
  `lampu_mati` varchar(20) DEFAULT NULL,
  `nyala_otomatis` varchar(20) DEFAULT NULL,
  `kondisi` enum('baik','rusak','','') NOT NULL,
  `catatan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_lampu`
--

INSERT INTO `master_lampu` (`id`, `code`, `line_area`, `lokasi`, `indikator_mati_menyala`, `lampu_mati`, `nyala_otomatis`, `kondisi`, `catatan`) VALUES
(1, 'LPE01', 'Factory A', 'OFFICE INVENTORY', 'Nyala', 'Tidak', 'Ya', 'baik', 'Mantap'),
(2, 'LE01', 'FA', 'OFFICE 1 (PINTU MASUK)', 'Nyala', 'Tidak', 'Tidak', 'baik', 'BAIK');

-- --------------------------------------------------------

--
-- Table structure for table `master_p3k`
--

CREATE TABLE `master_p3k` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `line_area` varchar(100) DEFAULT NULL,
  `lokasi` varchar(255) NOT NULL,
  `kondisi_kotak` enum('Baik','Tidak') NOT NULL DEFAULT 'Baik',
  `kelengkapan_isi` enum('Lengkap','Tidak') NOT NULL DEFAULT 'Lengkap',
  `expired_obat` enum('Lengkap','Tidak') NOT NULL DEFAULT 'Lengkap',
  `kondisi` enum('baik','rusak','','') NOT NULL,
  `catatan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `role` enum('admin','teknisi','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'Corinthian', 'Bogor', 'HSE', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agenda_inspeksi`
--
ALTER TABLE `agenda_inspeksi`
  ADD PRIMARY KEY (`id_agenda`),
  ADD KEY `id_lampu` (`id_lampu`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `area_line`
--
ALTER TABLE `area_line`
  ADD PRIMARY KEY (`id_line`);

--
-- Indexes for table `departemen`
--
ALTER TABLE `departemen`
  ADD PRIMARY KEY (`id_departemen`);

--
-- Indexes for table `inspeksi_eyewash`
--
ALTER TABLE `inspeksi_eyewash`
  ADD PRIMARY KEY (`id_inspeksi`),
  ADD KEY `code_eyewash` (`code_eyewash`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `inspeksi_lampu`
--
ALTER TABLE `inspeksi_lampu`
  ADD PRIMARY KEY (`id_inspeksi`),
  ADD KEY `code_lampu` (`code_lampu`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `inspeksi_lampu_exit`
--
ALTER TABLE `inspeksi_lampu_exit`
  ADD PRIMARY KEY (`id_inspeksi`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `inspeksi_p3k`
--
ALTER TABLE `inspeksi_p3k`
  ADD PRIMARY KEY (`id_inspeksi`),
  ADD KEY `code_p3k` (`code_p3k`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `master_eyewash`
--
ALTER TABLE `master_eyewash`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code` (`code`);

--
-- Indexes for table `master_lampu`
--
ALTER TABLE `master_lampu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code` (`code`);

--
-- Indexes for table `master_p3k`
--
ALTER TABLE `master_p3k`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agenda_inspeksi`
--
ALTER TABLE `agenda_inspeksi`
  MODIFY `id_agenda` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `area_line`
--
ALTER TABLE `area_line`
  MODIFY `id_line` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `departemen`
--
ALTER TABLE `departemen`
  MODIFY `id_departemen` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspeksi_eyewash`
--
ALTER TABLE `inspeksi_eyewash`
  MODIFY `id_inspeksi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspeksi_lampu`
--
ALTER TABLE `inspeksi_lampu`
  MODIFY `id_inspeksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `inspeksi_lampu_exit`
--
ALTER TABLE `inspeksi_lampu_exit`
  MODIFY `id_inspeksi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspeksi_p3k`
--
ALTER TABLE `inspeksi_p3k`
  MODIFY `id_inspeksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `master_eyewash`
--
ALTER TABLE `master_eyewash`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_lampu`
--
ALTER TABLE `master_lampu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `master_p3k`
--
ALTER TABLE `master_p3k`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inspeksi_eyewash`
--
ALTER TABLE `inspeksi_eyewash`
  ADD CONSTRAINT `fk_inspeksi_eyewash_code` FOREIGN KEY (`code_eyewash`) REFERENCES `master_eyewash` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inspeksi_eyewash_user` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `inspeksi_lampu`
--
ALTER TABLE `inspeksi_lampu`
  ADD CONSTRAINT `fk_inspeksi_lampu_code` FOREIGN KEY (`code_lampu`) REFERENCES `master_lampu` (`code`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `inspeksi_p3k`
--
ALTER TABLE `inspeksi_p3k`
  ADD CONSTRAINT `fk_inspeksi_p3k_code` FOREIGN KEY (`code_p3k`) REFERENCES `master_p3k` (`code`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
