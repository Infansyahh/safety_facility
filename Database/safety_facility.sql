-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql308.infinityfree.com
-- Generation Time: Aug 11, 2026 at 09:57 PM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_42292966_safety_facility`
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
(2, 'FA', '2026-07-02 00:42:28', 'lampu_emergency'),
(3, 'FA', '2026-07-06 03:39:17', 'lampu_exit'),
(4, 'FA', '2026-07-06 07:16:59', 'p3k'),
(5, 'FA', '2026-07-08 06:55:58', 'eyewash'),
(6, 'FB', '2026-07-13 03:59:20', 'lampu_emergency'),
(7, 'FC', '2026-07-13 03:59:27', 'lampu_emergency'),
(8, 'SECURITY', '2026-07-13 04:00:20', 'lampu_emergency'),
(9, 'FB', '2026-07-13 04:00:44', 'lampu_exit'),
(10, 'FC', '2026-07-13 04:00:49', 'lampu_exit'),
(11, 'MAINTENANCE', '2026-07-13 04:01:25', 'lampu_exit'),
(12, 'GLASS STORE', '2026-07-13 04:01:40', 'lampu_exit'),
(13, 'GLASS STORE', '2026-07-13 04:01:50', 'lampu_emergency'),
(14, 'FB', '2026-07-13 04:01:58', 'p3k'),
(15, 'FC', '2026-07-13 04:02:04', 'p3k'),
(17, 'MAINTENANCE', '2026-07-13 04:02:21', 'p3k'),
(18, 'SECURITY', '2026-07-13 04:02:38', 'p3k'),
(19, 'GLASS STORE', '2026-07-13 04:02:48', 'p3k'),
(20, 'FC', '2026-07-13 04:02:56', 'eyewash'),
(21, 'FB', '2026-07-13 04:03:00', 'eyewash'),
(22, 'MAINTENANCE', '2026-07-13 04:03:15', 'eyewash'),
(23, 'TPS', '2026-07-13 04:03:26', 'eyewash'),
(25, 'OFFICE', '2026-07-13 04:17:57', 'lampu_emergency'),
(26, 'OFFICE', '2026-07-13 07:34:33', 'p3k'),
(27, 'OFFICE', '2026-07-13 07:34:40', 'eyewash'),
(28, 'MAINTENANCE', '2026-07-14 07:38:46', 'lampu_emergency');

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

--
-- Dumping data for table `inspeksi_eyewash`
--

INSERT INTO `inspeksi_eyewash` (`id_inspeksi`, `code_eyewash`, `tanggal_inspeksi`, `kondisi`, `catatan`, `username`) VALUES
(9, 'EYE01', '2026-07-13', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus', 'Widiyantoro'),
(10, 'EYE05', '2026-07-13', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus', 'Widiyantoro'),
(14, 'EYE02', '2026-07-13', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus', 'Widiyantoro'),
(15, 'EYE03', '2026-07-13', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus', 'Widiyantoro'),
(16, 'EYE04', '2026-07-13', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus', 'Widiyantoro'),
(20, 'EYE06', '2026-07-13', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus', 'Widiyantoro');

-- --------------------------------------------------------

--
-- Table structure for table `inspeksi_lampu`
--

CREATE TABLE `inspeksi_lampu` (
  `id_inspeksi` int(11) NOT NULL,
  `code_lampu` varchar(50) NOT NULL,
  `indikator_mati_menyala` varchar(20) DEFAULT NULL,
  `lampu_mati` varchar(20) DEFAULT NULL,
  `nyala_otomatis` varchar(20) DEFAULT NULL,
  `tanggal_inspeksi` date NOT NULL,
  `kondisi` enum('baik','rusak','','') NOT NULL,
  `catatan` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inspeksi_lampu`
--

INSERT INTO `inspeksi_lampu` (`id_inspeksi`, `code_lampu`, `indikator_mati_menyala`, `lampu_mati`, `nyala_otomatis`, `tanggal_inspeksi`, `kondisi`, `catatan`, `username`) VALUES
(23, 'LPE01', 'Nyala', 'Tidak', 'Ya', '2026-07-14', 'baik', 'kondisi oke', 'Widiyantoro'),
(26, 'LPE02', 'Nyala', 'Tidak', 'Ya', '2026-07-14', 'baik', 'Lampu normal berfungsi semua', 'Arip'),
(27, 'LPE03', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Oke', 'Widiyantoro'),
(28, 'LPE05', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', '', 'Widiyantoro'),
(30, 'LPE10', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', '', 'Widiyantoro'),
(32, 'LPE47', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Ok', 'Widiyantoro'),
(33, 'LPE45', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Oke', 'Widiyantoro'),
(34, 'LPE43', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Oke', 'Widiyantoro'),
(36, 'LPE37', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', '', 'Arip'),
(38, 'LPE38', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Oke', 'Arip'),
(39, 'LPE34', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Ok', 'Arip'),
(40, 'LPE29', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Ok', 'Arip'),
(41, 'LPE27', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Ok', 'Arip'),
(42, 'LPE13', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Ok', 'Arip'),
(43, 'LPE14', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Ok', 'Arip'),
(44, 'LPE44', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Ok', 'Arip'),
(47, 'LPE09', 'Nyala', 'Tidak', 'Ya', '2026-07-16', 'baik', 'Ok', 'Arip'),
(55, 'LPE46', NULL, NULL, NULL, '2026-07-19', 'baik', 'Oke', 'Widiyantoro'),
(57, 'LPE62', NULL, NULL, NULL, '2026-07-23', 'baik', 'Oke', 'Widiyantoro'),
(58, 'LPE50', NULL, NULL, NULL, '2026-07-23', 'baik', '', 'Widiyantoro'),
(59, 'LPE52', NULL, NULL, NULL, '2026-07-23', 'baik', 'Oke', 'Widiyantoro'),
(60, 'LPE07', NULL, NULL, NULL, '2026-07-23', 'baik', 'Oke ', 'Widiyantoro'),
(61, 'LPE48', NULL, NULL, NULL, '2026-07-23', 'baik', 'OK ', 'Widiyantoro'),
(62, 'LPE51', NULL, NULL, NULL, '2026-07-23', 'baik', 'Oke', 'Widiyantoro');

-- --------------------------------------------------------

--
-- Table structure for table `inspeksi_lampu_exit`
--

CREATE TABLE `inspeksi_lampu_exit` (
  `id_inspeksi` int(11) NOT NULL,
  `id_lampu` varchar(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nama_operator` varchar(100) DEFAULT NULL,
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

--
-- Dumping data for table `inspeksi_p3k`
--

INSERT INTO `inspeksi_p3k` (`id_inspeksi`, `code_p3k`, `id_user`, `line_area`, `kondisi_kotak`, `kelengkapan_isi`, `expired_obat`, `keterangan`, `tanggal_inspeksi`, `kondisi`, `catatan`, `username`) VALUES
(15, 'P3K02', 1, 'OFFICE', 'Baik', 'Lengkap', 'Lengkap', 'Oke', '2026-07-13', 'baik', 'Oke', 'Widiyantoro'),
(16, 'P3K10', 1, 'FB', 'Baik', 'Lengkap', 'Lengkap', 'OKE', '2026-07-13', 'baik', 'OKE', 'Widiyantoro'),
(17, 'P3K03', 1, 'FA', 'Baik', 'Lengkap', 'Lengkap', '.oke', '2026-07-13', 'baik', '.oke', 'Widiyantoro'),
(19, 'P3K08', 1, 'GLASS STORE', 'Baik', 'Lengkap', 'Lengkap', 'Oke', '2026-07-13', 'baik', 'Oke', 'Widiyantoro'),
(21, 'P3K01', 1, 'OFFICE', 'Baik', 'Lengkap', 'Lengkap', 'Oke', '2026-07-13', 'baik', 'Oke', 'Widiyantoro'),
(22, 'P3K09', 1, 'SECURITY', 'Baik', 'Lengkap', 'Lengkap', 'Oke', '2026-07-13', 'baik', 'Oke', 'Widiyantoro'),
(23, 'P3K04', 1, 'FA', 'Baik', 'Lengkap', 'Lengkap', 'Oke', '2026-07-13', 'baik', 'Oke', 'Widiyantoro'),
(24, 'P3K07', 1, 'FC', 'Baik', 'Lengkap', 'Lengkap', 'Oke', '2026-07-13', 'baik', 'Oke', 'Widiyantoro'),
(25, 'P3K06', 1, 'MAINTENANCE', 'Baik', 'Lengkap', 'Lengkap', 'Oke', '2026-07-13', 'baik', 'Oke', 'Widiyantoro'),
(26, 'P3K05', 1, 'FB', 'Baik', 'Lengkap', 'Lengkap', 'Oke', '2026-07-13', 'baik', 'Oke', 'Widiyantoro'),
(27, 'P3K12', 1, 'FB', 'Baik', 'Lengkap', 'Lengkap', 'Oke', '2026-07-16', 'baik', 'Oke', 'Arip'),
(28, 'P3K11', 1, 'FB', 'Baik', 'Lengkap', 'Lengkap', 'Oke', '2026-07-16', 'baik', 'Oke', 'Arip');

-- --------------------------------------------------------

--
-- Table structure for table `master_eyewash`
--

CREATE TABLE `master_eyewash` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `line_area` varchar(100) DEFAULT NULL,
  `lokasi` varchar(255) NOT NULL,
  `kondisi` enum('baik','rusak','','') NOT NULL,
  `catatan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_eyewash`
--

INSERT INTO `master_eyewash` (`id`, `code`, `line_area`, `lokasi`, `kondisi`, `catatan`) VALUES
(3, 'EYE01', 'FA', 'BELAKANG TOILET FA', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus'),
(4, 'EYE02', 'FA', 'MAIN HOTPRESS', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus'),
(5, 'EYE03', 'FC', 'COMPRESOR', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus'),
(6, 'EYE04', 'FB', 'GUDANG BAHAN B3', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus'),
(7, 'EYE05', 'MAINTENANCE', 'MAINTENANCE', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus'),
(8, 'EYE06', 'TPS', 'TPS LIMBAH B3', 'baik', 'Aliran Lancar, Air Bersih, Kotak Bagus');

-- --------------------------------------------------------

--
-- Table structure for table `master_lampu`
--

CREATE TABLE `master_lampu` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `merek` enum('Visalux','Panasonic','Hokito','') DEFAULT NULL,
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

INSERT INTO `master_lampu` (`id`, `code`, `merek`, `line_area`, `lokasi`, `indikator_mati_menyala`, `lampu_mati`, `nyala_otomatis`, `kondisi`, `catatan`) VALUES
(9, 'LE01', NULL, 'FA', 'OFFICE 1 (PINTU MASUK)', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(10, 'LE02', NULL, 'FA', 'OFFICE PINTU HSE', 'Nyala', 'Tidak', 'Tidak', 'baik', 'Oke'),
(11, 'LE03', NULL, 'FA', 'OFFICE UTAMA FA', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(12, 'LE04', NULL, 'FA', 'OFFICE SSC', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(14, 'LE05', NULL, 'FA', 'PINTU 2 FA', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(15, 'LE06', NULL, 'FA', 'PINTU 3 FA', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(17, 'LE07', NULL, 'FA', 'PINTU 5 FA', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(18, 'LE08', NULL, 'FA', 'PINTU 6 FA', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(19, 'LE09', NULL, 'FB', 'PINTU UTAMA FB', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(20, 'LE10', NULL, 'FB', 'SAMPLE MAKER', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(21, 'LE11', NULL, 'MAINTENANCE', 'PINTU UTAMA MAINTENANCE', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(22, 'LE12', NULL, 'MAINTENANCE', 'PINTU 2 MAINTENANCE', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(23, 'LE13', NULL, 'FC', 'PINTU UTAMA FC', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(24, 'LE14', NULL, 'FC', 'PINTU 2 FC', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(25, 'LE15', NULL, 'FC', 'TIANG DEKAT RAK FC (2)', 'Nyala', 'Tidak', 'Tidak', 'baik', 'Ok'),
(26, 'LE16', NULL, 'FC', 'PINTU KELUAR YARD', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(27, 'LE17', NULL, 'GLASS STORE', 'PINTU MASUK RUANG KACA', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(28, 'LE18', NULL, 'FA', 'PINTU MASUK BOILER', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(31, 'LPE01', 'Panasonic', 'OFFICE', 'OFFICE STAFF', 'Nyala', 'Tidak', 'Ya', 'baik', 'kondisi oke'),
(32, 'LPE02', 'Panasonic', 'OFFICE', 'OFFICE HR', 'Nyala', 'Tidak', 'Ya', 'baik', 'Lampu normal berfungsi semua'),
(33, 'LPE03', 'Hokito', 'OFFICE', 'OFFICE PINTU TOILET', 'Nyala', 'Tidak', 'Ya', 'baik', 'Oke'),
(34, 'LPE04', NULL, 'OFFICE', 'RUANG SERVER', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(35, 'LPE05', 'Panasonic', 'OFFICE', 'LOBBY', 'Nyala', 'Tidak', 'Ya', 'baik', 'Ok'),
(36, 'LPE06', NULL, 'OFFICE', 'RUANG BU JUL', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(37, 'LPE07', 'Visalux', 'SECURITY', 'POS SECURITY', 'Nyala', 'Tidak', 'Ya', 'baik', 'Oke '),
(38, 'LPE08', NULL, 'SECURITY', 'RUANG BEA CUKAI', 'Nyala', 'Tidak', 'Tidak', 'baik', '.'),
(39, 'LPE09', 'Visalux', 'OFFICE', 'NPD 1', 'Nyala', 'Tidak', 'Ya', 'baik', 'Ok'),
(40, 'LPE10', 'Hokito', 'FA', 'TOILET FA', 'Nyala', 'Tidak', 'Ya', 'baik', 'Ok'),
(41, 'LPE11', NULL, 'FA', 'TANGGA GUDANG HR', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(42, 'LPE12', NULL, 'FA', 'SSC BAWAH', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(43, 'LPE13', NULL, 'FA', 'DEPAN SSC ATAS', 'Nyala', 'Tidak', 'Ya', 'baik', 'Ok'),
(51, 'LPE14', NULL, 'FA', 'SSC DALAM', 'Nyala', 'Tidak', 'Ya', 'baik', 'Ok'),
(52, 'LPE15', NULL, 'FA', 'TANGGA SSC', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(53, 'LPE16', NULL, 'FA', 'TOOL ROOM', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(54, 'LPE17', NULL, 'FA', 'UJUNG TIMUR LVMDP', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(55, 'LPE18', NULL, 'FA', 'RUANG LVMDP', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(56, 'LPE19', NULL, 'FA', 'RUANG GENSET 2', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(57, 'LPE20', NULL, 'FA', 'UJUNG BARAT LVMDP', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(58, 'LPE21', NULL, 'FA', 'H22B', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(59, 'LPE22', NULL, 'FA', 'TAILOR 2', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(61, 'LPE23', NULL, 'FA', 'SCHELLING 2', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(62, 'LPE24', NULL, 'FA', 'SUPPORT CENTER', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(63, 'LPE25', 'Hokito', 'FA', 'PINTU DET TIME SAVER', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(64, 'LPE26', NULL, 'FA', 'PINTU 2 FA', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(65, 'LPE27', NULL, 'FB', 'TRIBUN BARAT COMPRESOR', 'Nyala', 'Tidak', 'Ya', 'baik', 'Ok'),
(66, 'LPE28', NULL, 'FB', 'TIMUR (TRIBUN)', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(68, 'LPE29', NULL, 'FB', 'PINTU UTAMA FB', 'Nyala', 'Tidak', 'Ya', 'baik', 'Ok'),
(69, 'LPE30', NULL, 'FB', 'ADMIN SUBCON', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(70, 'LPE31', NULL, 'FB', 'FINISHING', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(71, 'LPE32', NULL, 'FB', 'LVMDP FB', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(72, 'LPE33', NULL, 'FB', 'OFFICE FB', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(73, 'LPE34', NULL, 'FB', 'SAMPLE MAKER', 'Nyala', 'Tidak', 'Ya', 'baik', 'Ok'),
(74, 'LPE35', NULL, 'MAINTENANCE', 'TANGGA MAINTENANCE', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(76, 'LPE37', NULL, 'FC', 'OFFICE FC', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(77, 'LPE38', NULL, 'FC', 'UNLOADING WH', 'Nyala', 'Tidak', 'Ya', 'baik', 'Oke'),
(78, 'LPE39', NULL, 'GLASS STORE', 'PINTU MASUK RUANG KACA', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(79, 'LPE40', NULL, 'GLASS STORE', 'RUANG KACA', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(80, 'LPE41', NULL, 'FA', 'PINTU KELUAR BOILER', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(81, 'LPE42', NULL, 'FA', 'LORONG GENSET', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(83, 'LPE43', NULL, 'OFFICE', 'RUANG MAHAGONY', 'Nyala', 'Tidak', 'Ya', 'baik', 'Oke'),
(84, 'LPE44', NULL, 'FA', 'TANGGA EMERGENCY SSC', 'Nyala', 'Tidak', 'Ya', 'baik', 'Ok'),
(85, 'LPE45', '', 'OFFICE', 'LOBBY LUAR', 'Nyala', 'Tidak', 'Ya', 'baik', 'Oke'),
(89, 'LPE46', 'Hokito', 'OFFICE', 'OFFICE HSE', 'Nyala', 'Tidak', 'Ya', 'baik', 'Oke'),
(90, 'LPE47', NULL, NULL, 'KANTIN 1', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(91, 'LPE48', 'Hokito', 'FA', 'KANTIN 2', 'Nyala', 'Tidak', 'Ya', 'baik', 'OK '),
(92, 'LPE49', 'Panasonic', 'FA', 'RUANG GENSET 1', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(93, 'LPE50', 'Visalux', 'OFFICE', 'OFFICE TOILET PINTU DEPAN', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(94, 'LPE51', 'Visalux', 'FA', 'DEPAN TOILET FA', 'Nyala', 'Tidak', 'Ya', 'baik', 'Oke'),
(95, 'LPE52', 'Hokito', 'OFFICE', 'RUANG MERANTI', 'Nyala', 'Tidak', 'Ya', 'baik', 'Oke'),
(96, 'LPE53', NULL, NULL, 'OFFICE EXIM', NULL, NULL, NULL, '', ''),
(97, 'LPE54', 'Visalux', 'FA', 'RUANG PANEL PLN 1', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(98, 'LPE55', 'Visalux', 'FA', 'RUANG PANEL PLN 2', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(99, 'LPE56', 'Visalux', 'FA', 'RUANG PANEL PLN 3', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(100, 'LPE57', 'Visalux', 'FB', 'STORE INVENTORY', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(101, 'LPE58', 'Visalux', 'FB', 'lVMDP FB LUAR', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(104, 'LPE59', 'Hokito', 'FA', 'RUANG POMPA HYDARNT LAMA', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(105, 'LPE60', 'Panasonic', 'FC', 'RUANG POMPA HYDARNT BARU 1', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(106, 'LPE61', 'Visalux', 'FC', 'RUANG POMPA HYDARNT BARU 2', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(107, 'LPE62', 'Hokito', 'OFFICE', 'TOILET OFFICE WANITA ', 'Nyala', 'Tidak', 'Ya', 'baik', 'Oke'),
(111, 'LPE65', 'Visalux', 'FC', 'WARE HOUSE GATE 1', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(112, 'LPE66', 'Visalux', 'FC', 'WARE HOUSE GATE 2', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(113, 'LPE67', 'Visalux', 'FC', 'WARE HOUSE GATE 3', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(114, 'LPE68', 'Visalux', 'FA', 'BOILER BESAR', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(115, 'LPE69', 'Visalux', 'FC', 'CRATING', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(116, 'LPE70', 'Hokito', 'FA', 'SAP KONFERMASI', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(117, 'LPE71', 'Panasonic', 'OFFICE', 'RUANG MANAGER NPD', 'Nyala', 'Tidak', 'Tidak', 'baik', ''),
(118, 'LPE72', 'Hokito', 'OFFICE', 'TOILET OFFICE PRIA', 'Nyala', 'Tidak', 'Ya', 'baik', ''),
(122, 'LPE73', 'Visalux', 'OFFICE', 'RUANG QUALITY', 'Nyala', 'Tidak', 'Ya', 'baik', 'Oke'),
(123, 'LPE74', 'Visalux', 'OFFICE', 'MANAGER NPD', 'Nyala', 'Tidak', 'Ya', 'baik', 'Oke'),
(124, 'LPE75', 'Visalux', 'FA', 'SCHELLING 2', 'Nyala', 'Tidak', 'Ya', 'baik', '');

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

--
-- Dumping data for table `master_p3k`
--

INSERT INTO `master_p3k` (`id`, `code`, `line_area`, `lokasi`, `kondisi_kotak`, `kelengkapan_isi`, `expired_obat`, `kondisi`, `catatan`) VALUES
(5, 'P3K01', 'OFFICE', 'OFFICE HSE', 'Baik', 'Lengkap', 'Lengkap', 'baik', 'Oke'),
(6, 'P3K02', 'OFFICE', 'NPD', 'Baik', 'Lengkap', 'Lengkap', 'baik', 'Oke'),
(7, 'P3K03', 'FA', 'TOOL ROOM', 'Baik', 'Lengkap', 'Lengkap', 'baik', '.oke'),
(8, 'P3K04', 'FA', 'SUPPORT CENTER', 'Baik', 'Lengkap', 'Lengkap', 'baik', 'Oke'),
(9, 'P3K05', 'FB', 'OFFICE FB', 'Baik', 'Lengkap', 'Lengkap', 'baik', 'Oke'),
(10, 'P3K06', 'MAINTENANCE', 'MAINTENANCE', 'Baik', 'Lengkap', 'Lengkap', 'baik', 'Oke'),
(11, 'P3K07', 'FC', 'OFFICE FC', 'Baik', 'Lengkap', 'Lengkap', 'baik', 'Oke'),
(15, 'P3K08', 'GLASS STORE', 'GLASS STORE', 'Baik', 'Lengkap', 'Lengkap', 'baik', 'Oke'),
(16, 'P3K09', 'SECURITY', 'POS SECURITY', 'Baik', 'Lengkap', 'Lengkap', 'baik', 'Oke'),
(17, 'P3K10', 'FB', 'STORE SPARE PART', 'Baik', 'Lengkap', 'Lengkap', 'baik', 'OKE'),
(18, 'P3K11', 'FB', 'SAMPLE MAKER', 'Baik', 'Lengkap', 'Lengkap', 'baik', 'Oke'),
(19, 'P3K12', 'FB', 'TPS LIMBAH B3', 'Baik', 'Lengkap', 'Lengkap', 'baik', 'Oke'),
(20, 'P3K13', 'OFFICE', 'SSC ATAS', 'Baik', 'Lengkap', 'Lengkap', 'baik', '');

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
  MODIFY `id_agenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `area_line`
--
ALTER TABLE `area_line`
  MODIFY `id_line` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `departemen`
--
ALTER TABLE `departemen`
  MODIFY `id_departemen` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspeksi_eyewash`
--
ALTER TABLE `inspeksi_eyewash`
  MODIFY `id_inspeksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `inspeksi_lampu`
--
ALTER TABLE `inspeksi_lampu`
  MODIFY `id_inspeksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `inspeksi_lampu_exit`
--
ALTER TABLE `inspeksi_lampu_exit`
  MODIFY `id_inspeksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inspeksi_p3k`
--
ALTER TABLE `inspeksi_p3k`
  MODIFY `id_inspeksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `master_eyewash`
--
ALTER TABLE `master_eyewash`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `master_lampu`
--
ALTER TABLE `master_lampu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `master_p3k`
--
ALTER TABLE `master_p3k`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
  ADD CONSTRAINT `fk_inspeksi_eyewash_code` FOREIGN KEY (`code_eyewash`) REFERENCES `master_eyewash` (`code`) ON DELETE CASCADE ON UPDATE CASCADE;

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
