-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 06 Jul 2026 pada 12.01
-- Versi server: 11.8.8-MariaDB-log
-- Versi PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u124793915_maua_project`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `addons`
--

CREATE TABLE `addons` (
  `id` varchar(50) NOT NULL,
  `label` varchar(150) NOT NULL,
  `worker_title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `task` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `schedule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `selected_package_id` bigint(20) UNSIGNED DEFAULT NULL,
  `selected_package_name` varchar(190) DEFAULT NULL,
  `selected_package_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `selected_package_subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `selected_package_destinations` longtext DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_whatsapp` varchar(30) DEFAULT NULL,
  `trip_type` enum('open','private') NOT NULL,
  `experience_type` enum('cave','custom') NOT NULL DEFAULT 'cave',
  `selected_date` date DEFAULT NULL,
  `visible_until` date DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `participants` int(11) NOT NULL DEFAULT 1,
  `price_per_person` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_type` varchar(20) DEFAULT NULL,
  `payment_status` varchar(40) DEFAULT NULL,
  `required_payment_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `bca_account_number` varchar(64) DEFAULT NULL,
  `status` enum('Menunggu Approval','Disetujui','Ditolak','Dibatalkan','Expired','Selesai') NOT NULL DEFAULT 'Menunggu Approval',
  `notes` text DEFAULT NULL,
  `transport_from` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `trip_id`, `schedule_id`, `session_id`, `selected_package_id`, `selected_package_name`, `selected_package_price`, `selected_package_subtotal`, `selected_package_destinations`, `customer_name`, `customer_email`, `customer_whatsapp`, `trip_type`, `experience_type`, `selected_date`, `visible_until`, `archived_at`, `start_time`, `end_time`, `participants`, `price_per_person`, `total_price`, `payment_type`, `payment_status`, `required_payment_amount`, `paid_amount`, `bca_account_number`, `status`, `notes`, `transport_from`, `created_at`, `updated_at`) VALUES
(24, 56, 34, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Putra 05', 'anugrahags05@gmail.com', '085702055011', 'open', 'cave', '2026-06-29', '2026-06-30', '2026-07-01 08:00:19', '08:12:00', '12:08:00', 1, 450000.00, 450000.00, 'dp', 'verified', 225000.00, 225000.00, '1234567890', 'Disetujui', '-', '', '2026-06-22 04:07:51', '2026-07-01 01:00:19'),
(25, 56, 33, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Putra 05', 'anugrahags05@gmail.com', '085702055011', 'open', 'cave', '2026-06-23', '2026-06-24', '2026-06-25 08:00:25', '15:15:00', '16:16:00', 1, 750000.00, 750000.00, 'dp', 'verified', 375000.00, 375000.00, '1234567890', 'Disetujui', '-', '', '2026-06-22 04:12:27', '2026-06-25 01:00:25'),
(26, 56, 39, NULL, 2, NULL, NULL, 0.00, 0.00, NULL, 'Putra 05', 'anugrahags05@gmail.com', '085702055011', 'private', 'cave', '2026-07-14', '2026-07-15', NULL, '08:00:00', '12:00:00', 1, 1500000.00, 2000000.00, 'dp', 'rejected', 1000000.00, 1000000.00, '4561504789', 'Expired', '-', '', '2026-06-23 13:27:05', '2026-06-28 05:57:11'),
(27, 59, 36, 12, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Saputra 04', 'anugrahags04@gmail.com', '085702055011', 'open', 'cave', '2026-07-02', '2026-07-03', '2026-07-04 08:00:26', '08:00:00', '13:00:00', 1, 500000.00, 500000.00, 'dp', 'rejected', 250000.00, 250000.00, '4561504789', 'Ditolak', '-', '', '2026-06-24 10:50:37', '2026-07-04 01:00:26'),
(28, 59, 36, 22, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Saputra 04', 'anugrahags04@gmail.com', '085702055011', 'open', 'cave', '2026-07-12', '2026-07-13', NULL, '08:00:00', '13:00:00', 3, 500000.00, 2350000.00, 'dp', 'rejected', 1175000.00, 1175000.00, '4561504789', 'Ditolak', '-', '', '2026-06-24 11:23:16', '2026-06-24 11:24:22'),
(29, 59, 34, 8, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Saputra 04', 'anugrahags04@gmail.com', '085702055011', 'open', 'cave', '2026-07-12', '2026-07-13', NULL, '08:00:00', '13:00:00', 1, 450000.00, 450000.00, 'full', 'rejected', 450000.00, 450000.00, '4561504789', 'Dibatalkan', '-', '', '2026-06-24 15:34:04', '2026-07-02 06:32:19'),
(30, 62, 33, 56, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Hanifah', 'hanyamanusiabiasa04@gmail.com', '081393611933', 'open', 'cave', '2026-07-12', '2026-07-13', NULL, '08:00:00', '12:00:00', 1, 750000.00, 750000.00, 'dp', 'verified', 375000.00, 375000.00, '4561504789', 'Disetujui', '-', '', '2026-06-25 03:53:25', '2026-06-25 04:00:05'),
(31, 65, 33, 60, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Azizah Suwitaningtyas Azzahra', 'azizaharhazza@gmail.com', '085748489200', 'open', 'cave', '2026-07-26', '2026-07-27', NULL, '08:00:00', '12:00:00', 1, 750000.00, 950000.00, 'dp', 'verified', 475000.00, 475000.00, '4561504789', 'Disetujui', '-', '', '2026-06-25 03:59:55', '2026-06-25 04:59:18'),
(32, 63, 33, 58, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Evelyn Tan', 'evelyntannnn@gmail.com', '081230744536', 'open', 'cave', '2026-07-19', '2026-07-20', NULL, '08:00:00', '12:00:00', 5, 750000.00, 3750000.00, 'dp', 'verified', 1875000.00, 1875000.00, '4561504789', 'Disetujui', '-', '', '2026-06-25 04:11:09', '2026-06-25 04:58:16'),
(33, 69, 33, 4, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Siti Muzayanah', 'anamuzayanah000@gmail.com', '081227774394', 'open', 'cave', '2026-07-05', '2026-07-06', NULL, '08:00:00', '12:00:00', 1, 750000.00, 750000.00, 'dp', 'verified', 375000.00, 375000.00, '4561504789', 'Disetujui', '-', '', '2026-06-25 05:14:55', '2026-06-25 05:41:09'),
(34, 68, 33, 4, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Mufti Hidayat Amin', 'muftidakota90@gmail.com', '081228323703', 'open', 'cave', '2026-07-05', '2026-07-06', NULL, '08:00:00', '12:00:00', 1, 750000.00, 950000.00, 'dp', 'verified', 475000.00, 475000.00, '4561504789', 'Disetujui', '-', '', '2026-06-25 05:17:16', '2026-06-25 05:40:58'),
(35, 70, 33, 56, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Febriana Indra Ayu Kirana', 'kirananona1516@gmail.com', '082140701846', 'open', 'cave', '2026-07-12', '2026-07-13', NULL, '08:00:00', '12:00:00', 1, 750000.00, 1200000.00, 'dp', 'verified', 600000.00, 600000.00, '4561504789', 'Disetujui', '-', '', '2026-06-25 05:43:48', '2026-06-25 05:48:34'),
(36, 83, 33, 59, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Ummi Kalsum', 'ummikalsumn@gmail.com', '085163525498', 'open', 'cave', '2026-07-19', '2026-07-20', NULL, '13:30:00', '17:30:00', 1, 750000.00, 1200000.00, 'dp', 'verified', 600000.00, 600000.00, '4561504789', 'Disetujui', '-', '', '2026-06-25 05:52:57', '2026-06-25 05:57:44'),
(37, 83, 36, 30, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Ummi Kalsum', 'ummikalsumn@gmail.com', '085163525498', 'open', 'cave', '2026-07-20', '2026-07-21', NULL, '08:00:00', '13:00:00', 1, 500000.00, 700000.00, 'dp', 'verified', 350000.00, 350000.00, '4561504789', 'Disetujui', '-', '', '2026-06-25 05:54:18', '2026-06-25 05:57:14'),
(38, 109, 33, 59, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Rifka Wangiana Yulia Putri', 'aerisexol6@gmail.com', '083861293708', 'open', 'cave', '2026-07-19', '2026-07-20', NULL, '13:30:00', '17:30:00', 1, 750000.00, 750000.00, 'full', 'verified', 750000.00, 750000.00, '4561504789', 'Disetujui', '-', '', '2026-06-25 07:19:35', '2026-06-25 08:31:06'),
(39, 110, 33, 60, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Yusia Harnanda', 'yusiaharnanda0@gmail.com', '085880063476', 'open', 'cave', '2026-07-26', '2026-07-27', NULL, '08:00:00', '12:00:00', 1, 750000.00, 750000.00, 'dp', 'verified', 375000.00, 375000.00, '4561504789', 'Disetujui', '-', '', '2026-06-25 07:36:31', '2026-06-25 08:31:21'),
(40, 111, 33, 56, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Al Fitra Salim As Syifa', 'alfitraasyifa@gmail.com', '089512461392', 'open', 'cave', '2026-07-12', '2026-07-13', NULL, '08:00:00', '12:00:00', 1, 750000.00, 750000.00, 'dp', 'verified', 375000.00, 375000.00, '4561504789', 'Disetujui', '-', '', '2026-06-25 08:16:22', '2026-06-25 08:29:11'),
(41, 115, 36, 16, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Thalia Charisma', 'thaliacharisma@gmail.com', '081231539850', 'open', 'cave', '2026-07-06', '2026-07-07', NULL, '08:00:00', '13:00:00', 2, 500000.00, 2800000.00, 'dp', 'verified', 1400000.00, 1400000.00, '4561504789', 'Disetujui', 'request pickup & dokumentasi lengkap', '', '2026-06-26 00:57:26', '2026-06-26 11:59:28'),
(42, 70, 36, 21, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Febriana Indra Ayu Kirana', 'kirananona1516@gmail.com', '082140701846', 'open', 'cave', '2026-07-11', '2026-07-12', NULL, '08:00:00', '13:00:00', 2, 500000.00, 2800000.00, 'full', 'verified', 2800000.00, 2800000.00, '4561504789', 'Disetujui', '-', '', '2026-06-26 02:27:43', '2026-06-26 11:58:41'),
(43, 121, 33, 61, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Devina Mellysa Octaviasari', 'devinamellysaocta11@gmail.com', '085792935097', 'open', 'cave', '2026-07-26', '2026-07-27', NULL, '13:30:00', '17:30:00', 1, 750000.00, 900000.00, 'dp', 'rejected', 450000.00, 450000.00, '4561504789', 'Ditolak', '-', '', '2026-06-27 03:08:59', '2026-06-27 11:26:10'),
(44, 112, 33, 56, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Arvin Sujitno', 'sujitnoarvin@gmail.com', '085155288835', 'open', 'cave', '2026-07-12', '2026-07-13', NULL, '08:00:00', '12:00:00', 1, 750000.00, 750000.00, 'dp', 'verified', 375000.00, 375000.00, '4561504789', 'Disetujui', '-', '', '2026-06-27 07:21:59', '2026-06-27 11:22:06'),
(45, 122, 33, 4, NULL, NULL, NULL, 0.00, 0.00, NULL, 'RAHILDA NURUL SAKINAH', 'rahildanrls@gmail.com', '081317404452', 'open', 'cave', '2026-07-05', '2026-07-06', NULL, '08:00:00', '12:00:00', 2, 750000.00, 1500000.00, 'dp', 'verified', 750000.00, 750000.00, '4561504789', 'Disetujui', '-', '', '2026-06-27 11:29:04', '2026-06-27 11:35:04'),
(46, 121, 33, 61, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Devina Mellysa Octaviasari', 'devinamellysaocta11@gmail.com', '085792935097', 'open', 'cave', '2026-07-26', '2026-07-27', NULL, '13:30:00', '17:30:00', 1, 750000.00, 900000.00, 'dp', 'verified', 450000.00, 450000.00, '4561504789', 'Disetujui', '-', '', '2026-06-27 11:43:37', '2026-06-28 05:36:34'),
(47, 121, 33, 61, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Devina Mellysa Octaviasari', 'devinamellysaocta11@gmail.com', '085792935097', 'open', 'cave', '2026-07-26', '2026-07-27', NULL, '13:30:00', '17:30:00', 1, 750000.00, 900000.00, 'dp', 'rejected', 450000.00, 450000.00, '4561504789', 'Dibatalkan', '-', '', '2026-06-27 11:44:44', '2026-06-28 05:36:31'),
(48, 124, 33, 5, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Nafisa Hidayah', 'nafisahdyh@gmail.com', '081568392871', 'open', 'cave', '2026-07-05', '2026-07-06', NULL, '13:30:00', '17:30:00', 1, 750000.00, 1100000.00, 'full', 'verified', 1100000.00, 1100000.00, '4561504789', 'Disetujui', '-', '', '2026-06-27 12:18:46', '2026-06-28 05:34:32'),
(49, 123, 33, 5, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Putu Alini Pratiwi', 'aliniipratiwii2709@gmail.com', '081337683674', 'open', 'cave', '2026-07-05', '2026-07-06', NULL, '13:30:00', '17:30:00', 1, 750000.00, 950000.00, 'dp', 'verified', 475000.00, 475000.00, '4561504789', 'Disetujui', '-', '', '2026-06-27 12:54:55', '2026-06-28 05:34:02'),
(50, 70, 36, 21, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Fanani Rahmiyah Ariwigati', 'kirananona1516@gmail.com', '082140701846', 'open', 'cave', '2026-07-11', '2026-07-12', NULL, '08:00:00', '13:00:00', 2, 500000.00, 1000000.00, 'full', 'verified', 1000000.00, 1000000.00, '4561504789', 'Disetujui', '-', '', '2026-06-29 09:18:12', '2026-06-29 10:21:10'),
(51, 131, 33, 5, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Aina Sarah Hafawati', 'sarahaina2023@gmail.com', '082134804769', 'open', 'cave', '2026-07-05', '2026-07-06', NULL, '13:30:00', '17:30:00', 1, 750000.00, 750000.00, 'dp', 'verified', 375000.00, 375000.00, '4561504789', 'Disetujui', '-', '', '2026-06-29 12:09:29', '2026-06-29 12:16:10'),
(52, 133, 45, NULL, 22, NULL, NULL, 0.00, 0.00, NULL, 'Meimeimecin', 'tikonerlia@gmail.com', '082155530596', 'private', 'cave', '2026-07-17', '2026-07-18', NULL, '13:30:00', '17:30:00', 3, 850000.00, 2550000.00, 'dp', 'verified', 1275000.00, 1275000.00, '4561504789', 'Disetujui', '1 peserta tidak makan nasi \n', '', '2026-06-30 01:45:14', '2026-06-30 01:48:26'),
(53, 133, 36, 27, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Meimeimecin', 'tikonerlia@gmail.com', '082155530596', 'open', 'cave', '2026-07-17', '2026-07-18', NULL, '08:00:00', '13:00:00', 2, 500000.00, 2800000.00, 'dp', 'verified', 1400000.00, 1400000.00, '4561504789', 'Disetujui', '-', '', '2026-06-30 01:56:07', '2026-06-30 01:58:38'),
(54, 134, 33, 57, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Ainun silvi', 'ainun_silvi@icloud.com', '085950293907', 'open', 'cave', '2026-07-12', '2026-07-13', NULL, '13:30:00', '17:30:00', 3, 750000.00, 2250000.00, 'dp', 'verified', 1125000.00, 1125000.00, '4561504789', 'Disetujui', '-', '', '2026-06-30 05:39:26', '2026-06-30 05:51:25'),
(55, 135, 33, 57, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Zara', 'zaahroo16@gmail.com', '081214022975', 'open', 'cave', '2026-07-12', '2026-07-13', NULL, '13:30:00', '17:30:00', 1, 750000.00, 950000.00, 'dp', 'verified', 475000.00, 475000.00, '4561504789', 'Disetujui', '-', '', '2026-06-30 08:35:34', '2026-06-30 08:38:49'),
(56, 137, 33, 57, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Retno Wijayanti', 'rtnwy5705@gmail.com', '089513112424', 'open', 'cave', '2026-07-12', '2026-07-13', NULL, '13:30:00', '17:30:00', 1, 750000.00, 750000.00, 'dp', 'verified', 375000.00, 375000.00, '4561504789', 'Disetujui', '-', '', '2026-06-30 11:40:38', '2026-06-30 13:16:22'),
(57, 138, 33, 60, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Garin Christi Saputri', 'saputrigarin@gmail.com', '08119934848', 'open', 'cave', '2026-07-26', '2026-07-27', NULL, '08:00:00', '12:00:00', 1, 750000.00, 1150000.00, 'dp', 'verified', 575000.00, 575000.00, '4561504789', 'Disetujui', '-', '', '2026-06-30 13:12:32', '2026-06-30 13:16:32'),
(58, 117, 33, 5, NULL, NULL, NULL, 0.00, 0.00, NULL, 'ANANDA FEBRINAWATI', 'waterfalljello.ndn@gmail.com', '085122683229', 'open', 'cave', '2026-07-05', '2026-07-06', NULL, '13:30:00', '17:30:00', 1, 750000.00, 900000.00, 'full', 'rejected', 900000.00, 900000.00, '4561504789', 'Dibatalkan', '-', '', '2026-07-01 05:18:33', '2026-07-01 09:39:05'),
(59, 139, 42, NULL, 7, NULL, NULL, 0.00, 0.00, NULL, 'Nita Silfianah', 'nitasilfianah@gmail.com', '085771678734', 'private', 'custom', '2026-07-03', '2026-07-04', '2026-07-05 08:00:22', '08:00:00', '12:00:00', 3, 300000.00, 1200000.00, 'dp', 'verified', 600000.00, 600000.00, '4561504789', 'Disetujui', '-', '', '2026-07-01 07:50:15', '2026-07-05 01:00:22'),
(60, 117, 33, 5, NULL, NULL, NULL, 0.00, 0.00, NULL, 'ANANDA FEBRINAWATI', 'waterfalljello.ndn@gmail.com', '085122683229', 'open', 'cave', '2026-07-05', '2026-07-06', NULL, '13:30:00', '17:30:00', 1, 750000.00, 750000.00, 'full', 'rejected', 750000.00, 750000.00, '4561504789', 'Dibatalkan', '-', '', '2026-07-01 09:25:34', '2026-07-01 09:38:09'),
(61, 117, 33, 5, NULL, NULL, NULL, 0.00, 0.00, NULL, 'ANANDA FEBRINAWATI', 'waterfalljello.ndn@gmail.com', '085122683229', 'open', 'cave', '2026-07-05', '2026-07-06', NULL, '13:30:00', '17:30:00', 1, 750000.00, 750000.00, 'full', 'verified', 750000.00, 750000.00, '4561504789', 'Disetujui', '-', '', '2026-07-01 09:51:02', '2026-07-01 09:56:46'),
(62, 141, 36, 14, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Kiveileen Nofa Malindo', 'nkiveileen2@gmail.com', '081293944712', 'open', 'cave', '2026-07-04', '2026-07-05', '2026-07-06 08:00:25', '08:00:00', '13:00:00', 1, 500000.00, 700000.00, 'dp', 'verified', 350000.00, 350000.00, '4561504789', 'Disetujui', '-', '', '2026-07-02 03:05:54', '2026-07-06 01:00:25'),
(63, 142, 33, 5, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Marseila Puspita Dewi', 'marseilapuspita2707@gmail.com', '088219779232', 'open', 'cave', '2026-07-05', '2026-07-06', NULL, '13:30:00', '17:30:00', 2, 750000.00, 1500000.00, 'dp', 'verified', 750000.00, 750000.00, '4561504789', 'Disetujui', '-', '', '2026-07-02 03:21:11', '2026-07-02 03:25:45'),
(64, 144, 34, 8, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Ine Lestari', 'inelestari51@gmail.com', '082320857353', 'open', 'cave', '2026-07-12', '2026-07-13', NULL, '08:00:00', '13:00:00', 1, 450000.00, 450000.00, 'full', 'verified', 450000.00, 450000.00, '4561504789', 'Disetujui', 'Tidak ada', '', '2026-07-02 06:30:52', '2026-07-02 06:31:58'),
(65, 145, 33, 60, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Rengganingtyas', 'rengganingt@gmail.com', '089612134312', 'open', 'cave', '2026-07-26', '2026-07-27', NULL, '08:00:00', '12:00:00', 2, 750000.00, 1500000.00, 'dp', 'verified', 750000.00, 750000.00, '4561504789', 'Disetujui', '-', '', '2026-07-02 12:06:19', '2026-07-02 13:31:42'),
(66, 140, 36, 14, NULL, NULL, NULL, 0.00, 0.00, NULL, 'resty pranandari', 'restiananda10@gmail.com', '082216719070', 'open', 'cave', '2026-07-04', '2026-07-05', '2026-07-06 08:00:25', '08:00:00', '13:00:00', 1, 500000.00, 700000.00, 'full', 'verified', 700000.00, 700000.00, '4561504789', 'Disetujui', 'penjemputan penginapan di pondok ino guest house, prawirotaman', '', '2026-07-03 03:55:23', '2026-07-06 01:00:25'),
(67, 150, 36, 14, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Santi Rachmawati', 'ibnukevran@gmail.com', '081328960950', 'open', 'cave', '2026-07-04', '2026-07-05', '2026-07-06 08:00:25', '08:00:00', '13:00:00', 1, 500000.00, 500000.00, 'dp', 'verified', 250000.00, 250000.00, '4561504789', 'Disetujui', '-', '', '2026-07-03 10:08:47', '2026-07-06 01:00:25'),
(68, 152, 33, 59, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Olivia Audrey', 'oliviaaudreyy@gmail.com', '087888080896', 'open', 'cave', '2026-07-19', '2026-07-20', NULL, '13:30:00', '17:30:00', 1, 750000.00, 750000.00, 'dp', 'verified', 375000.00, 375000.00, '4561504789', 'Disetujui', '-', '', '2026-07-04 14:20:17', '2026-07-05 01:30:46'),
(69, 151, 33, 59, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Mar\'atus Sholehah', 'marsolika@gmail.com', '081390021819', 'open', 'cave', '2026-07-19', '2026-07-20', NULL, '13:30:00', '17:30:00', 1, 750000.00, 750000.00, 'dp', 'verified', 375000.00, 375000.00, '4561504789', 'Disetujui', '--', '', '2026-07-04 15:12:31', '2026-07-05 01:30:36'),
(70, 154, 36, 17, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Elisya Hileri', 'ehileritei@gmail.com', '087888997655', 'open', 'cave', '2026-07-07', '2026-07-08', NULL, '08:00:00', '13:00:00', 2, 500000.00, 1250000.00, 'dp', 'verified', 625000.00, 625000.00, '4561504789', 'Disetujui', '-', '', '2026-07-05 05:10:17', '2026-07-05 05:40:34'),
(71, 149, 33, 61, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Firdha Widya Sari', 'firdawidya123go@gmail.com', '085607879797', 'open', 'cave', '2026-07-26', '2026-07-27', NULL, '13:30:00', '17:30:00', 1, 750000.00, 750000.00, 'dp', 'verified', 375000.00, 375000.00, '4561504789', 'Disetujui', '-', '', '2026-07-05 10:25:02', '2026-07-05 10:30:01'),
(72, 150, 34, 9, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Santi Rachmawati', 'ibnukevran@gmail.com', '081328960950', 'open', 'cave', '2026-07-26', '2026-07-27', NULL, '08:00:00', '13:00:00', 1, 450000.00, 450000.00, 'dp', 'verified', 225000.00, 225000.00, '4561504789', 'Disetujui', '-', '', '2026-07-05 15:31:54', '2026-07-06 03:52:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_addons`
--

CREATE TABLE `booking_addons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `addon_id` varchar(50) DEFAULT NULL,
  `trip_addon_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `booking_addons`
--

INSERT INTO `booking_addons` (`id`, `booking_id`, `addon_id`, `trip_addon_id`, `quantity`, `price`) VALUES
(1, 26, NULL, 217, 1, 500000.00),
(2, 28, NULL, 198, 1, 850000.00),
(3, 31, NULL, 179, 1, 200000.00),
(4, 34, NULL, 179, 1, 200000.00),
(5, 35, NULL, 177, 1, 250000.00),
(6, 35, NULL, 179, 1, 200000.00),
(7, 36, NULL, 177, 1, 250000.00),
(8, 36, NULL, 179, 1, 200000.00),
(9, 37, NULL, 199, 1, 200000.00),
(10, 41, NULL, 194, 1, 950000.00),
(11, 41, NULL, 198, 1, 850000.00),
(12, 42, NULL, 194, 1, 950000.00),
(13, 42, NULL, 198, 1, 850000.00),
(14, 43, NULL, 178, 1, 150000.00),
(15, 46, NULL, 178, 1, 150000.00),
(16, 47, NULL, 178, 1, 150000.00),
(17, 48, NULL, 178, 1, 150000.00),
(18, 48, NULL, 179, 1, 200000.00),
(19, 49, NULL, 179, 1, 200000.00),
(20, 53, NULL, 194, 1, 950000.00),
(21, 53, NULL, 198, 1, 850000.00),
(22, 55, NULL, 179, 1, 200000.00),
(23, 57, NULL, 177, 1, 250000.00),
(24, 57, NULL, 178, 1, 150000.00),
(25, 58, NULL, 178, 1, 150000.00),
(26, 59, NULL, 235, 1, 300000.00),
(27, 62, NULL, 199, 1, 200000.00),
(28, 66, NULL, 199, 1, 200000.00),
(29, 70, NULL, 197, 1, 250000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_participants`
--

CREATE TABLE `booking_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `health_notes` text DEFAULT NULL,
  `blood_type` varchar(20) DEFAULT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `weight_kg` decimal(6,2) DEFAULT NULL,
  `shoe_size` decimal(4,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `booking_participants`
--

INSERT INTO `booking_participants` (`id`, `booking_id`, `name`, `email`, `whatsapp`, `address`, `age`, `gender`, `health_notes`, `blood_type`, `height_cm`, `weight_kg`, `shoe_size`) VALUES
(4, 24, 'Putra 05', 'anugrahags05@gmail.com', '085702055011', 'Yogyakarta', 22, 'Laki-laki', '-', 'A', 178.00, 73.00, 43.0),
(5, 25, 'Putra 05', 'anugrahags05@gmail.com', '085702055011', 'Yogyakarta', 22, 'Laki-laki', '-', 'A', 178.00, 73.00, 43.0),
(6, 26, 'Putra 05', 'anugrahags05@gmail.com', '085702055011', 'Yogyakarta', 22, 'Laki-laki', '-', 'A', 178.00, 73.00, 43.0),
(7, 27, 'Saputra 04', 'anugrahags04@gmail.com', '085702055011', 'Yogyakarta', 22, 'Laki-laki', 'Sehat Walafiat', 'A', 178.00, 73.00, 43.0),
(8, 28, 'Saputra 04', 'anugrahags04@gmail.com', '085702055011', 'Yogyakarta', 22, 'Laki-laki', 'Sehat Walafiat', 'A', 178.00, 73.00, 43.0),
(9, 28, 'tes', 'tes@gmail.com', '098321023', 'Tes', 11, 'Laki-laki', 'tes', 'A', 170.00, 60.00, 43.0),
(10, 28, 'Pelem', 'pelem@gmail.com', '032139103213', 'Pelem', 15, 'Laki-laki', 'Pelem', 'A', 150.00, 40.00, 43.0),
(11, 29, 'Saputra 04', 'anugrahags04@gmail.com', '085702055011', 'Yogyakarta', 22, 'Laki-laki', 'Sehat Walafiat', 'A', 178.00, 73.00, 43.0),
(12, 30, 'Hanifah', 'hanyamanusiabiasa04@gmail.com', '081393611933', 'Koripan 1, Dlingo', 28, 'Perempuan', '-', 'B', 160.00, 60.00, 37.0),
(13, 31, 'Azizah Suwitaningtyas Azzahra', 'azizaharhazza@gmail.com', '085748489200', 'Madiun', 26, 'Perempuan', 'tidak ada', 'O', 159.00, 48.00, 40.0),
(14, 32, 'Evelyn Tan', 'evelyntannnn@gmail.com', '081230744536', 'Jl Argopuro No 43', 27, 'Perempuan', '-', 'O', 160.00, 48.00, 38.0),
(15, 32, 'Cynthia Anggraini', 'cynthiaanggraeni88@gmail.com', '08999289295', 'Taman Pondok Jati BF 01', 27, 'Perempuan', '-', 'O', 160.00, 47.00, 38.0),
(16, 32, 'felicia natalie tjoanda', 'felicianataliet@gmail.com', '087855163850', 'perumahan pondok intan permai 1 D2', 27, 'Perempuan', '-', 'B', 159.00, 49.00, 38.0),
(17, 32, 'christina', 'christinaanggraeni024@gmail.com', '0895345241201', 'Taman Pondok Jati BF/1 Sepanjang, Jawa Timur', 24, 'Perempuan', '-', 'O', 170.00, 52.00, 40.0),
(18, 32, 'david tan kayogi', 'davidtankayogi@gmail.com', '081214996100', 'perumahan pondok intan permai 1 D2', 35, 'Laki-laki', '-', 'B', 170.00, 79.00, 43.0),
(19, 33, 'Siti Muzayanah', 'anamuzayanah000@gmail.com', '081227774394', 'Rembang', 26, 'Perempuan', '‘_’', 'A', 157.00, 65.00, 41.0),
(20, 34, 'Mufti Hidayat Amin', 'muftidakota90@gmail.com', '081228323703', 'Semarang', 35, 'Laki-laki', 'asam lambung', 'Tidak tahu', 166.00, 52.00, 41.0),
(21, 35, 'Febriana Indra Ayu Kirana', 'kirananona1516@gmail.com', '082140701846', 'Surabaya', 23, 'Perempuan', '-', 'B', 164.00, 45.00, 36.5),
(22, 36, 'Ummi Kalsum', 'ummikalsumn@gmail.com', '085163525498', 'jl paccerakkang 135', 28, 'Perempuan', 'asam lambung', 'A', 163.00, 72.00, 40.0),
(23, 37, 'Ummi Kalsum', 'ummikalsumn@gmail.com', '085163525498', 'jl paccerakkang 135', 28, 'Perempuan', 'asam lambung', 'A', 163.00, 72.00, 40.0),
(24, 38, 'Rifka Wangiana Yulia Putri', 'aerisexol6@gmail.com', '083861293708', 'Jl Merdeka 92 Pasuruhan Binangun', 29, 'Perempuan', '-', 'O', 155.00, 44.00, 37.0),
(25, 39, 'Yusia Harnanda', 'yusiaharnanda0@gmail.com', '085880063476', 'Banjarbaru ', 29, 'Perempuan', '-', 'B', 158.00, 47.00, 37.0),
(26, 40, 'Al Fitra Salim As Syifa', 'alfitraasyifa@gmail.com', '089512461392', 'Jl. Dinar Mas XVI/18 Semarang', 26, 'Perempuan', '-', 'O', 156.00, 55.00, 37.0),
(27, 41, 'Thalia Charisma', 'thaliacharisma@gmail.com', '081231539850', 'Jakarta', 28, 'Perempuan', '-', 'A', 160.00, 54.00, 38.0),
(28, 41, 'Faadhila Syafi', 'thaliacharisma@yahoo.com', '+62 852-1001-2705', 'Surabaya', 28, 'Perempuan', '-', 'B', 156.00, 40.00, 38.0),
(29, 42, 'Febriana Indra Ayu Kirana', 'kirananona1516@gmail.com', '082140701846', 'Surabaya', 23, 'Perempuan', '-', 'B', 164.00, 45.00, 37.0),
(30, 42, 'Karina Fitri Aji', 'karina.fitri60@gmail.com', '085694735867', 'Jl. H. Dero No 92', 20, 'Perempuan', '-', 'A', 165.00, 68.00, 38.0),
(31, 43, 'Devina Mellysa Octaviasari', 'devinamellysaocta11@gmail.com', '085792935097', 'Malang', 26, 'Perempuan', 'Tidak ada', 'O', 165.00, 54.00, 40.0),
(32, 44, 'Arvin Sujitno', 'sujitnoarvin@gmail.com', '085155288835', 'Jalan Thamrin Gang Lawang 1 No. 40-A', 27, 'Laki-laki', '-', 'B', 185.00, 77.00, 45.0),
(33, 45, 'RAHILDA NURUL SAKINAH', 'rahildanrls@gmail.com', '081317404452', 'Jakarta selatan', 29, 'Perempuan', '-', 'B', 168.00, 79.00, 40.0),
(34, 45, 'MUHAMMAD FADHIL ADITYA', 'm.fadhil200401@gmail.com', '08116654200', 'JAKARTA BARAT', 25, 'Laki-laki', '-', 'O', 180.00, 84.00, 43.0),
(35, 46, 'Devina Mellysa Octaviasari', 'devinamellysaocta11@gmail.com', '085792935097', 'Malang', 26, 'Perempuan', 'Tidak ada', 'O', 165.00, 54.00, 40.0),
(36, 47, 'Devina Mellysa Octaviasari', 'devinamellysaocta11@gmail.com', '085792935097', 'Malang', 26, 'Perempuan', 'Tidak ada', 'O', 165.00, 54.00, 40.0),
(37, 48, 'Nafisa Hidayah', 'nafisahdyh@gmail.com', '081568392871', 'Sukoharjo Jawa Tengah', 19, 'Perempuan', '-', 'O', 155.00, 42.00, 39.0),
(38, 49, 'Putu Alini Pratiwi', 'aliniipratiwii2709@gmail.com', '081337683674', 'Bali, Denpasar', 17, 'Perempuan', '-', 'A', 160.00, 48.00, 39.0),
(39, 50, 'Fanani Rahmiyah Ariwigati', 'annora.malayeka25@gmail.com', '082216888910', 'Surabaya', 35, 'Perempuan', '-', 'B', 161.00, 51.00, 38.0),
(40, 50, 'Indramawan Kusuma Trisna', 'annora.malayeka25@gmail.com', '08112727127', 'Surabaya', 39, 'Laki-laki', '-', 'O', 170.00, 80.00, 42.0),
(41, 51, 'Aina Sarah Hafawati', 'sarahaina2023@gmail.com', '082134804769', 'Magelang', 21, 'Perempuan', '-', 'Tidak tahu', 163.00, 65.00, 41.0),
(42, 52, 'Meimeimecin', 'tikonerlia@gmail.com', '082155530596', 'Jakarta', 30, 'Perempuan', '-', 'O', 163.00, 47.00, 38.0),
(43, 52, 'Kristanto', 'tikonerlia@gmail.com', '082155530596', 'Jakarta', 30, 'Laki-laki', '-', 'O', 180.00, 82.00, 43.0),
(44, 52, 'Ocdeliany', 'ocdelianyedisetiawan@gmail.com', '082155530596', 'Jakarta', 30, 'Perempuan', '-', 'O', 160.00, 98.00, 39.0),
(45, 53, 'Meimeimecin', 'tikonerlia@gmail.com', '082155530596', 'Jakarta', 30, 'Perempuan', '-', 'O', 163.00, 47.00, 38.0),
(46, 53, 'Kristanto', 'tikonerlia@gmail.com', '082155530596', 'Jakarta', 30, 'Laki-laki', '-', 'A', 180.00, 82.00, 43.0),
(47, 54, 'Ainun silvi', 'ainun_silvi@icloud.com', '085950293907', 'Cirebon', 26, 'Perempuan', '-', 'A', 157.00, 53.00, 39.0),
(48, 54, 'Deafrisa Nugrahati', 'deafrisan@gmail.com', '089515107000', 'Wonogiri', 27, 'Perempuan', 'Tipes', 'B', 155.00, 43.00, 38.0),
(49, 54, 'Selma Oktaviani', 'selmaoktaviani77@gmail.com', '088806844489', 'Purwokerto', 19, 'Perempuan', '-', 'A', 150.00, 49.00, 38.0),
(50, 55, 'Zara', 'zaahroo16@gmail.com', '081214022975', 'Jakarta Selatan', 28, 'Perempuan', '-', 'O', 153.00, 68.00, 40.0),
(51, 56, 'Retno Wijayanti', 'rtnwy5705@gmail.com', '089513112424', 'Grobogan ', 20, 'Perempuan', '-', 'Tidak tahu', 164.00, 63.00, 39.0),
(52, 57, 'Garin Christi Saputri', 'saputrigarin@gmail.com', '08119934848', 'Jakarta/Solo', 33, 'Perempuan', '-', 'B', 156.00, 48.00, 37.0),
(53, 58, 'ANANDA FEBRINAWATI', 'waterfalljello.ndn@gmail.com', '085122683229', 'GG. BROTOSENO 1, DUSUN III, PUCANGAN, KARTASURA, SUKOHARJO, JAWA TENGAH', 20, 'Perempuan', '-', 'B', 151.00, 46.00, 39.0),
(54, 59, 'Nita Silfianah', 'nitasilfianah@gmail.com', '085771678734', 'Bekasi', 28, 'Perempuan', '-', 'Tidak tahu', 161.00, 60.00, 39.5),
(55, 59, 'Yulia Saviskaya', 'lilinilam36@gmail.com', '087889675532', 'Jl. Bakti Warga 2 No.86, RT.001/RW.004, Jatiranggon (Cat kuning no 83) JATI SAMPURNA, KOTA BEKASI, JAWA BARAT, ID 17432', 27, 'Perempuan', 'Asam lambung', 'Tidak tahu', 169.00, 70.00, 40.5),
(56, 59, 'Mela Kresnawati', 'kresnawatimela@gmail.com', '082217011305', 'KP pondok ranggon RT 007/RW 003 nomer 10, Jatimurni, Pondok Melati, Kota Bekasi ', 28, 'Perempuan', 'Asma', 'B', 160.00, 100.00, 41.0),
(57, 60, 'ANANDA FEBRINAWATI', 'waterfalljello.ndn@gmail.com', '085122683229', 'GG. BROTOSENO 1, DUSUN III, PUCANGAN, KARTASURA, SUKOHARJO, JAWA TENGAH', 20, 'Perempuan', '-', 'B', 151.00, 46.00, 39.0),
(58, 61, 'ANANDA FEBRINAWATI', 'waterfalljello.ndn@gmail.com', '085122683229', 'GG. BROTOSENO 1, DUSUN III, PUCANGAN, KARTASURA, SUKOHARJO, JAWA TENGAH', 20, 'Perempuan', '-', 'B', 151.00, 46.00, 39.0),
(59, 62, 'Kiveileen Nofa Malindo', 'nkiveileen2@gmail.com', '081293944712', 'Pekanbaru', 26, 'Perempuan', '-', 'A', 153.00, 49.00, 37.0),
(60, 63, 'Marseila Puspita Dewi', 'marseilapuspita2707@gmail.com', '088219779232', 'Bekasi', 22, 'Perempuan', 'darah rendah', 'Tidak tahu', 154.00, 49.00, 39.0),
(61, 63, 'Nurul fajrina syafrial', 'nrlfjrn19@gmail.com', '081511897246', 'bekasi', 26, 'Perempuan', 'darah rendah', 'Tidak tahu', 158.00, 61.00, 39.0),
(62, 64, 'Ine Lestari', 'inelestari51@gmail.com', '082320857353', 'Kebumen', 29, 'Perempuan', 'Tidak ada', 'O', 160.00, 55.00, 40.0),
(63, 65, 'Rengganingtyas', 'rengganingt@gmail.com', '089612134312', 'Yogyakarta', 25, 'Perempuan', '-', 'A', 158.00, 43.00, 39.0),
(64, 65, 'Nasya', 'dwisekarkarto@gmail.com', '085191257568', 'Yogyakarta ', 23, 'Perempuan', '-', 'A', 160.00, 47.00, 40.0),
(65, 66, 'resty pranandari', 'restiananda10@gmail.com', '082216719070', 'tangerang kita', 32, 'Perempuan', '-', 'A', 161.00, 52.00, 39.0),
(66, 67, 'Santi Rachmawati', 'ibnukevran@gmail.com', '081328960950', 'Jogjakarta', 43, 'Perempuan', '-', 'A', 160.00, 53.00, 39.0),
(67, 68, 'Olivia Audrey', 'oliviaaudreyy@gmail.com', '087888080896', 'Jakarta', 29, 'Perempuan', '-', 'A', 150.00, 52.00, 36.0),
(68, 69, 'Mar\'atus Sholehah', 'marsolika@gmail.com', '081390021819', 'Karanganyar', 29, 'Perempuan', 'Tidak ada', 'O', 155.00, 65.00, 38.0),
(69, 70, 'Elisya Hileri', 'ehileritei@gmail.com', '087888997655', 'Jl. Kemang Raya Selatan, gg. Bersama No. 8', 30, 'Perempuan', '‘-‘', 'Tidak tahu', 160.00, 48.00, 37.0),
(70, 70, 'Joseph Bambang Nugorho', 'josephbnugroho@gmail.com', '081288393294', 'Jl. Kemang Raya Selatan, Gg. Bersama No.8', 34, 'Laki-laki', '‘-‘', 'Tidak tahu', 178.00, 76.00, 43.0),
(71, 71, 'Firdha Widya Sari', 'firdawidya123go@gmail.com', '085607879797', 'Pasuruan', 24, 'Perempuan', '-', 'A', 153.00, 67.00, 40.0),
(72, 72, 'Santi Rachmawati', 'ibnukevran@gmail.com', '081328960950', 'Jogjakarta', 43, 'Perempuan', '-', 'A', 160.00, 53.00, 39.0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `email_logs`
--

CREATE TABLE `email_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recipient_email` varchar(150) NOT NULL,
  `email_type` enum('reminder','invoice','approval','rejection') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `email_verification_tokens`
--

CREATE TABLE `email_verification_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expired_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `package_price_tiers`
--

CREATE TABLE `package_price_tiers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `pax_count` int(10) UNSIGNED NOT NULL,
  `price_per_person` decimal(14,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `package_price_tiers`
--

INSERT INTO `package_price_tiers` (`id`, `package_id`, `pax_count`, `price_per_person`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 500000.00, '2026-06-21 11:41:31', '2026-06-28 07:01:48'),
(2, 2, 1, 400000.00, '2026-06-21 11:41:31', '2026-06-28 07:01:48'),
(3, 3, 1, 450000.00, '2026-06-21 11:41:31', '2026-06-28 07:01:48'),
(4, 4, 1, 450000.00, '2026-06-21 11:41:31', '2026-06-28 07:01:48'),
(5, 5, 1, 450000.00, '2026-06-21 11:41:31', '2026-06-28 07:01:48'),
(6, 6, 1, 550000.00, '2026-06-21 11:41:31', '2026-06-28 07:01:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(100) DEFAULT NULL,
  `payment_proof_url` text DEFAULT NULL,
  `payment_status` varchar(40) NOT NULL DEFAULT 'waiting_verification',
  `submitted_at` datetime DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `amount`, `payment_method`, `payment_proof_url`, `payment_status`, `submitted_at`, `verified_at`) VALUES
(2, 24, 225000.00, 'qris_or_bca', 'https://mauaproject.com/uploads/payment-proofs/74f1bf65a9641c0bd14ca79494543650.jpg', 'verified', '2026-06-22 11:07:51', NULL),
(3, 25, 375000.00, 'qris_or_bca', 'https://mauaproject.com/uploads/payment-proofs/9ff62a646ea9b7dbde6b1a74de60a31a.jpg', 'verified', '2026-06-22 11:12:27', NULL),
(4, 26, 1000000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/af205d62a8aa6a8f108a2bfa7b4e2f05.png', 'rejected', '2026-06-23 20:27:05', NULL),
(5, 27, 250000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/ce6864470a1107538ff9b2283222c6a4.png', 'rejected', '2026-06-24 17:50:37', NULL),
(6, 28, 1175000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/52f3f0ad38e03f378b27e22da5d4d69c.png', 'rejected', '2026-06-24 18:23:16', NULL),
(7, 29, 450000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/59ecb7fb25b0c317fa456acecaede88c.png', 'rejected', '2026-06-24 22:34:04', NULL),
(8, 30, 375000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/22637bcde7a7416e8a3cf2da2a02c1b0.jpg', 'verified', '2026-06-25 10:53:25', NULL),
(9, 31, 475000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/a24559c8983882c4de1a35d703c2d777.png', 'verified', '2026-06-25 10:59:55', NULL),
(10, 32, 1875000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/214b871276535851a81a195d9f70e75e.jpg', 'verified', '2026-06-25 11:11:09', NULL),
(11, 33, 375000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/34fdf50bbac0966c7991926ba9bb8b70.png', 'verified', '2026-06-25 12:14:55', NULL),
(12, 34, 475000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/7a8c4a2530cf1282e8dad1cf9b2b5a25.jpg', 'verified', '2026-06-25 12:17:16', NULL),
(13, 35, 600000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/f91b15d4fa3d9b2fa7be2ababd7c1053.png', 'verified', '2026-06-25 12:43:48', NULL),
(14, 36, 600000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/520f9d9a669993e3336eb4124b19464c.jpg', 'verified', '2026-06-25 12:52:57', NULL),
(15, 37, 350000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/ee9932ddd6e1fe009a913eda01d3c864.jpg', 'verified', '2026-06-25 12:54:18', NULL),
(16, 38, 750000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/fffd7396c2a78ef2b2267c54ac03c14c.jpg', 'verified', '2026-06-25 14:19:35', NULL),
(17, 39, 375000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/5d3d633001626fab89d110a3e755f7d1.jpg', 'verified', '2026-06-25 14:36:31', NULL),
(18, 40, 375000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/451ec6dfcaff418459e09d0672019ea0.jpg', 'verified', '2026-06-25 15:16:22', NULL),
(19, 41, 1400000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/d0eb9644d96490db580389097f305116.png', 'verified', '2026-06-26 07:57:26', NULL),
(20, 42, 2800000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/d4855c9d50bc3916072cdfe0793aebee.png', 'verified', '2026-06-26 09:27:43', NULL),
(21, 43, 450000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/74f89d34aa8f3688a1f635d50e409925.jpg', 'rejected', '2026-06-27 10:08:59', NULL),
(22, 44, 375000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/606f4a6fe05fbebb11db976b7d4a9d84.jpg', 'verified', '2026-06-27 14:21:59', NULL),
(23, 45, 750000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/fea0740d20b3e528c1ac72f0e4c3687c.jpg', 'verified', '2026-06-27 18:29:04', NULL),
(24, 46, 450000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/4f1c91424692ca061e826a31b0521548.jpg', 'verified', '2026-06-27 18:43:37', NULL),
(25, 47, 450000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/96e27af2d93614679ad958f1611c6d95.jpg', 'rejected', '2026-06-27 18:44:44', NULL),
(26, 48, 1100000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/e22b2e497aeb0dc79226b24cac40fdf5.jpg', 'verified', '2026-06-27 19:18:46', NULL),
(27, 49, 475000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/d8c2cbfccb865439ff4dbb5a4c785d58.jpg', 'verified', '2026-06-27 19:54:55', NULL),
(28, 50, 1000000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/155522fb4c4b77d9967098e8ca15f937.png', 'verified', '2026-06-29 16:18:12', NULL),
(29, 51, 375000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/87808d9909440034b115edecfb7c3a0b.jpg', 'verified', '2026-06-29 19:09:29', NULL),
(30, 52, 1275000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/2981b38c3899408619bc4f888bc43bcb.png', 'verified', '2026-06-30 08:45:14', NULL),
(31, 53, 1400000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/eeb41446a5f5c06d857e18cc9b80f2fe.jpg', 'verified', '2026-06-30 08:56:07', NULL),
(32, 54, 1125000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/5d3ecef90317d20e8ba5c77b6058220a.jpg', 'verified', '2026-06-30 12:39:26', NULL),
(33, 55, 475000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/b06ae9b2f6f2162e486a14f7fce75568.jpg', 'verified', '2026-06-30 15:35:34', NULL),
(34, 56, 375000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/9725af7e2014bcf292120576277bbb18.jpg', 'verified', '2026-06-30 18:40:38', NULL),
(35, 57, 575000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/67eb5166675df5f0a9660469b8ea8eaf.png', 'verified', '2026-06-30 20:12:32', NULL),
(36, 58, 900000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/3c808b3a30f6b8a7476ab7d07f2e1d19.png', 'rejected', '2026-07-01 12:18:33', NULL),
(37, 59, 600000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/45d8d01185ad1842aba21fe7caaf2e4b.jpg', 'verified', '2026-07-01 14:50:15', NULL),
(38, 60, 750000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/e1e27eed8ab4a51d3b0ad91474b9a713.png', 'rejected', '2026-07-01 16:25:34', NULL),
(39, 61, 750000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/5c6e4fa80530f86dc29cda6e52259f7b.png', 'verified', '2026-07-01 16:51:02', NULL),
(40, 62, 350000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/6e59e43df8fa6e540f72053a3c41c752.jpg', 'verified', '2026-07-02 10:05:54', NULL),
(41, 63, 750000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/e788d7986796190cc0847ec181f15af9.jpg', 'verified', '2026-07-02 10:21:11', NULL),
(42, 64, 450000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/1bea19a8a68628baefa2cf32c78be6ae.jpg', 'verified', '2026-07-02 13:30:52', NULL),
(43, 65, 750000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/d50599fb653d90c4375ca1f4236322ea.png', 'verified', '2026-07-02 19:06:19', NULL),
(44, 66, 700000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/7860e233737cadbad6ae642fa4ed21c5.jpg', 'verified', '2026-07-03 10:55:23', NULL),
(45, 67, 250000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/cff05af8b8783eaace37bc6477c226f1.jpg', 'verified', '2026-07-03 17:08:47', NULL),
(46, 68, 375000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/da5c4566272ec5ba63c070de98f64e3f.jpg', 'verified', '2026-07-04 21:20:17', NULL),
(47, 69, 375000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/de1a959edccd6b1fa95c513cb914ce85.jpg', 'verified', '2026-07-04 22:12:31', NULL),
(48, 70, 625000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/eeeacefdb96e4aade5eb8a972b991844.png', 'verified', '2026-07-05 12:10:17', NULL),
(49, 71, 375000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/bb605846a95af7daf6f369f73e042015.jpg', 'verified', '2026-07-05 17:25:03', NULL),
(50, 72, 225000.00, 'bca', 'https://mauaproject.com/uploads/payment-proofs/2afe0fad1095c2f6c29363a6fd4cbc98.jpg', 'verified', '2026-07-05 22:31:54', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pending_customer_registrations`
--

CREATE TABLE `pending_customer_registrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `health_notes` text DEFAULT NULL,
  `blood_type` varchar(20) DEFAULT NULL,
  `height_cm` smallint(5) UNSIGNED DEFAULT NULL,
  `weight_kg` decimal(5,2) UNSIGNED DEFAULT NULL,
  `shoe_size` decimal(4,1) UNSIGNED DEFAULT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expired_at` datetime NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `last_sent_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pending_customer_registrations`
--

INSERT INTO `pending_customer_registrations` (`id`, `name`, `email`, `password_hash`, `whatsapp`, `address`, `age`, `gender`, `health_notes`, `blood_type`, `height_cm`, `weight_kg`, `shoe_size`, `otp_hash`, `expired_at`, `attempts`, `last_sent_at`, `created_at`, `updated_at`) VALUES
(14, 'Fara Fatwa Rosandi', 'claaaastya1112@gmail.com', '$2y$10$nZFIKrlI1L3X/z/a.yDdVOVMqJW2XuZEpab5cf1vdQbNIv9kLCIAe', '088983607424', 'klaten', 17, 'Perempuan', 'tidak ada ', 'B', 165, 56.00, 40.0, '$2y$10$F5aQp03cFgpr5E1Q4d.QwuVy44LM2t0jBKU.r0iwLMZYFYO638AnW', '2026-06-25 13:07:51', 0, '2026-06-25 12:37:51', '2026-06-25 05:35:55', '2026-06-25 05:37:51'),
(15, 'Fara Fatwa Rosandi', 'molatrishamola@gmail.com', '$2y$10$lk00cgsWlJLFLMbwUfC9pecm2TZlIWfrriBlJ9nwhyT3niWK/qPLe', '088983607424', 'Klaten', 17, 'Perempuan', 'Tidak ada', 'B', 165, 57.00, 40.0, '$2y$10$Kzjd1Lsp9ZG/.XX/2TjaMutkvZDjd/XjUcSr3XV3duO23hp3iujWe', '2026-06-25 13:11:15', 0, '2026-06-25 12:41:15', '2026-06-25 05:39:37', '2026-06-25 05:41:15'),
(18, 'Yusia Harnanda', 'yusiaharnanda96@icloud.com', '$2y$10$XOYQ/pIzLbT3loKDs8DK8OS2RxSshkxWO1Kz/YTewCj.ky9eTyNgK', '085880063475', 'Banjarbaru ', 29, 'Perempuan', '-', 'B', 158, 47.00, 37.0, '$2y$10$nJ5eVY9IplxL7cqAYOdPmuAaYOKgXM1XXN1U4ZtwD9R1exAG/Kr9K', '2026-06-25 14:58:15', 0, '2026-06-25 14:28:15', '2026-06-25 07:27:15', '2026-06-25 07:28:15'),
(22, 'Ananda Febrinawati', 'anandafebrinawatii@gmail.com', '$2y$10$iB0BI4MI/Kr4gQs3tA1.fe9Q.LaTWMBgYIUWpxiooppfxg45KWxga', '085122683229', 'Gg Brotoseno 1, Dusun III, Pucangan, Kartasura, Sukoharjo, Jawa Tengah ', 20, 'Perempuan', '-', 'B', 151, 46.00, 39.0, '$2y$10$tT8RRIS.XMSHu5kBQ7INTuySDpCPznM1hALjPkQOM3maoYHr/S8Je', '2026-06-25 17:28:35', 0, '2026-06-25 16:58:35', '2026-06-25 09:57:32', '2026-06-25 09:58:35'),
(31, 'Rezalina Defi Arta Mevia', 'rezalinamevia@gmail.com', '$2y$10$mYPG7VahAMAjXAXMjG37keQ5TURqgKFYxYWcm.PWCFal..arpPN1m', '085923246386', 'gresik', 21, 'Perempuan', '-', 'B', 157, 42.00, 38.0, '$2y$10$U5XZXGYqOvi5nKZiRvRCQ.NVwqZSqcqbJfi/SQW2blFO1eqdfCAwi', '2026-07-03 10:23:27', 0, '2026-07-03 09:53:27', '2026-06-27 06:01:28', '2026-07-03 02:53:27'),
(35, 'Muhammad Fadhil Aditya', 'm.fadhil200401@gmail.com', '$2y$10$3IcEhL9qHdJZmJE1d2E1CuIunMlecPfXJeZxz40nSWIAwDp8OSuTm', '08116654200', 'Jakarta', 25, 'Laki-laki', '-', 'O', 180, 83.00, 43.0, '$2y$10$UFaaGHLnan.K4UIt8FGAOe/wLVTwggpFGAOUDOC8GNxMEBYfQRXDG', '2026-06-27 18:50:00', 0, '2026-06-27 18:20:00', '2026-06-27 11:20:00', '2026-06-27 11:20:00'),
(36, 'Nafisa Hidayah', 'asaskara81@gmail.com', '$2y$10$4jUTcThY28VWarHYjb8B9.yyjKFLJ2IDxMHvqjJ/HjOxhcsQr7C2y', '081568392871', 'Sukoharjo Jawa Tengah', 19, 'Perempuan', '', 'O', 153, 42.00, 39.0, '$2y$10$YGySBDav0gLGmOvdJ.x9ou3//T5Aq.HfJJ6rBvpfVE4qgV7S9VhCe', '2026-06-27 19:21:41', 0, '2026-06-27 18:51:41', '2026-06-27 11:50:27', '2026-06-27 11:51:41'),
(38, 'Putu Alini Pratiwi', 'aliniabg@gmail.com', '$2y$10$0vh.6xBNh5Fqg7mauYJ8XOCKg6Pz.1dmGkQFlK4hrolhMUeHgGl9q', '081337683674', 'Denpasar', 17, 'Perempuan', '-', 'A', 160, 48.00, 39.0, '$2y$10$J/m4QAtA7UEBHooQ1Ir5e.qesHozIG7mBv6qozHvBpuLyPti2hA.W', '2026-06-27 19:24:28', 0, '2026-06-27 18:54:28', '2026-06-27 11:54:28', '2026-06-27 11:54:28'),
(39, 'Putu Alini Pratiwi', 'aliniinila2709@gmail.com', '$2y$10$pvfGO93Tdh3gwDZg2vqyTOBNvqE2f8WS9qyC9BO4J6DdL.waCsnhe', '081337683674', 'Denpasar', 17, 'Perempuan', '-', 'A', 160, 48.00, 39.0, '$2y$10$mqL0hmszAnCc9X93JnraD.Cafie9NkX6WtZ4477XuyzNYwbT8c5dW', '2026-06-27 19:27:00', 0, '2026-06-27 18:57:00', '2026-06-27 11:55:47', '2026-06-27 11:57:00'),
(42, 'Putu Alini Pratiwi', 'alinalun2727@gmail.con', '$2y$10$EdcSqEL9v9nqKWOBE6ntOevveK90HZeMWwxV2rP839mK8PVpOjmDa', '081337683674', 'Denpasar', 17, 'Perempuan', '-', 'A', 160, 48.00, 39.0, '$2y$10$whzpOmbiTF8ooSm/AlQzOOws04WjzphdrUNykCnCb0IpwCwZzSrga', '2026-06-27 19:33:47', 0, '2026-06-27 19:03:47', '2026-06-27 12:03:47', '2026-06-27 12:03:47'),
(48, 'Aina Sarah Hafawati', 'ainasarahhafawati@gmail.com', '$2y$10$hV6bvr1xVCSWfd/yCP4ZwunDHrv9NwF9tOgd/XLSl2hm4Cka6Cjda', '082134804769', 'Magelang', 21, 'Perempuan', 'sehat', 'Tidak tahu', 163, 65.00, 41.0, '$2y$10$IZSTYG0fcF3IEj/nMZXJmOhQ7vXvQqbobvawKZ2jVTtFTZmxHy/Bq', '2026-06-29 17:18:22', 0, '2026-06-29 16:48:22', '2026-06-29 09:46:26', '2026-06-29 09:48:22'),
(51, 'Anisa Melia R', 'anisameliauii10@gmail.com', '$2y$10$R5jvXei3UQTqh.qC6t79O.lFfkWOB2xszoCjXqk9SfvWx15jBKwJO', '081327396845', 'Klaten', 23, 'Perempuan', 'tidak ada', 'O', 166, 68.00, 39.0, '$2y$10$MNR.x1QpNXRxNlXUUV6UnefrZG0e30jI926Qvp/u482c/n3Qt8Lhe', '2026-06-30 07:23:42', 0, '2026-06-30 06:53:42', '2026-06-29 23:53:42', '2026-06-29 23:53:42'),
(53, 'Deafrisa Nugrahati', 'deafrisan@gmail.com', '$2y$10$wfoUokybBFLjJIkm8pqQZuH78SiGv2zYbI7bZmJ5mnHIQar9qf0pG', '089515107000', 'Magelang', 27, 'Perempuan', '-', 'B', 155, 43.00, 38.0, '$2y$10$JGagCrik0pAEO855i0iaYectcE1mHZlpIFP9QioL7H2PSXJKxpsGm', '2026-06-30 12:16:37', 0, '2026-06-30 11:46:37', '2026-06-30 04:44:59', '2026-06-30 04:46:37'),
(54, 'Ainun silvi', 'ainunsilfi65@gmail.com', '$2y$10$Rc12YdJPjRMY2lbK3WD3UOtJW0prjgcevEj1/DCzWr1pnluRPJZGW', '085950293907', 'Cirebon', 26, 'Perempuan', '', 'A', 155, 53.00, 39.0, '$2y$10$5kZHDCL7IGYoGz00gEvELu5sMwLrpKcOcW5yOP.XuFFNla4JAHq3K', '2026-06-30 12:20:18', 0, '2026-06-30 11:50:18', '2026-06-30 04:50:18', '2026-06-30 04:50:18'),
(63, 'BILQIST ALMA FADHILAH', 'bilqistalma@gmail.com', '$2y$10$.ng4kVuv4mE2GQpc7QvO7uLQNDwb1.SE/xpRCo4gpEYYvpSH8x0L2', '081217767494', 'SURABAYA', 22, 'Perempuan', 'Asam Lambung', 'B', 150, 43.00, 37.0, '$2y$10$XPKNVwzT/di407PrZeemMulB5F4A.INcqSfHn6P1eyI2Aims/yT3m', '2026-07-01 15:00:26', 0, '2026-07-01 14:30:26', '2026-07-01 07:30:26', '2026-07-01 07:30:26'),
(64, 'Ayudia khairunnisa', 'ayudiakhairunnisa@gmail.com', '$2y$10$CYI4DZtXc.MelRSd.mbib.AWlg72h9tTE/KCVQw0srk1dqt4pNBXi', '089618364401', 'Jakarta selatan', 25, 'Perempuan', '', NULL, 165, 56.00, 38.5, '$2y$10$z9DjUswbtycBBI684UifhOnfVl54/emxHAK6sM6cXwg6sw6.c3.Um', '2026-07-01 15:07:19', 0, '2026-07-01 14:37:19', '2026-07-01 07:37:19', '2026-07-01 07:37:19'),
(71, 'Rengganingtyas', 'rengganingt@gmail.con', '$2y$10$HeEYgDZqeM5UdLSBWi6Et.4rykYoUjVUXimhdewTa/Db1kTLq9m6W', '089612134312', 'Yogyakarta ', 25, '', '', NULL, 158, 43.00, 39.0, '$2y$10$tuh0.2XKi9h.UR/5nHQnJekw1CzmBUpo5Q0YvHb31i03wNgTirdcO', '2026-07-02 18:03:55', 0, '2026-07-02 17:33:55', '2026-07-02 10:26:58', '2026-07-02 10:33:55'),
(83, 'Hesti Pratiwi', 'hestiugm@gmail.com', '$2y$10$T9HBMCsFi8RPqbko.J8jduqgzyG/WwGckrJjjKHUu5kNhNMjS4Jx.', '081227528520', 'Yogyakarta', 37, 'Perempuan', 'Tidak ada', 'O', 160, 57.00, 38.0, '$2y$10$93V5XXrtqGJmHngALb16D.JT/HDoZsYnxgB.a0M2WOFqxHOD68Kbm', '2026-07-05 11:13:22', 0, '2026-07-05 10:43:22', '2026-07-05 03:43:22', '2026-07-05 03:43:22'),
(84, 'Hesti Pratiwi', 'hesti.ugm@gmail.com', '$2y$10$RIljh4gPFQVUzzwhua.feuliPNARREsgMb3AGHCZBJ6ROkQUWeqtq', '081227528520', 'Bantul', 38, 'Perempuan', 'Tidak ada', 'O', 160, 57.00, 38.0, '$2y$10$4R1fYvU1k9Vw6ICtgX.PJuSRcVyngyIxLNU00SRLmF77Wrt.j11kS', '2026-07-05 11:16:14', 0, '2026-07-05 10:46:14', '2026-07-05 03:46:14', '2026-07-05 03:46:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `private_price_tiers`
--

CREATE TABLE `private_price_tiers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `pax_count` int(11) NOT NULL,
  `price_per_person` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `private_price_tiers`
--

INSERT INTO `private_price_tiers` (`id`, `trip_id`, `pax_count`, `price_per_person`) VALUES
(70, 39, 1, 1500000.00),
(71, 39, 2, 750000.00),
(72, 39, 3, 750000.00),
(73, 39, 4, 750000.00),
(74, 39, 5, 750000.00),
(75, 40, 1, 1350000.00),
(76, 40, 2, 675000.00),
(77, 40, 3, 450000.00),
(78, 40, 4, 450000.00),
(79, 40, 5, 450000.00),
(80, 40, 6, 450000.00),
(81, 40, 7, 450000.00),
(82, 40, 8, 450000.00),
(83, 41, 1, 2550000.00),
(84, 41, 2, 1275000.00),
(85, 41, 3, 850000.00),
(86, 41, 4, 850000.00),
(87, 41, 5, 850000.00),
(88, 41, 6, 750000.00),
(89, 41, 7, 750000.00),
(90, 41, 8, 750000.00),
(91, 41, 9, 750000.00),
(92, 41, 10, 750000.00),
(93, 42, 1, 600000.00),
(94, 42, 2, 300000.00),
(95, 42, 3, 375000.00),
(96, 42, 4, 375000.00),
(97, 42, 5, 375000.00),
(98, 42, 6, 375000.00),
(99, 42, 7, 375000.00),
(100, 42, 8, 375000.00),
(101, 42, 9, 375000.00),
(102, 42, 10, 375000.00),
(103, 44, 1, 120000.00),
(104, 44, 2, 120000.00),
(105, 44, 3, 120000.00),
(106, 44, 4, 120000.00),
(107, 44, 5, 120000.00),
(108, 44, 6, 120000.00),
(109, 44, 7, 120000.00),
(110, 44, 8, 120000.00),
(111, 44, 9, 120000.00),
(112, 44, 10, 120000.00),
(113, 38, 1, 500000.00),
(114, 43, 1, 400000.00),
(115, 43, 2, 400000.00),
(116, 43, 3, 400000.00),
(117, 43, 4, 400000.00),
(118, 45, 1, 3400000.00),
(119, 45, 2, 1700000.00),
(120, 45, 3, 1135000.00),
(121, 45, 4, 850000.00),
(122, 45, 5, 850000.00),
(123, 46, 1, 4150000.00),
(124, 46, 2, 2450000.00),
(125, 46, 3, 1883000.00),
(126, 46, 4, 1600000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `private_trip_packages`
--

CREATE TABLE `private_trip_packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `package_code` varchar(80) NOT NULL,
  `name` varchar(190) NOT NULL,
  `name_en` varchar(190) DEFAULT NULL,
  `price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `max_custom_pax` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `destinations_json` longtext NOT NULL,
  `destinations_json_en` longtext DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `private_trip_packages`
--

INSERT INTO `private_trip_packages` (`id`, `trip_id`, `package_code`, `name`, `name_en`, `price`, `max_custom_pax`, `destinations_json`, `destinations_json_en`, `description`, `description_en`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 43, 'SUNRISE_TRIP', 'Sunrise Trip', 'Sunrise Trip', 500000.00, 1, '[\"Bunker Kaliadem\",\"Batu Alien\",\"Museum Sisa Hartaku\",\"Track Air Kali Kuning\"]', '[\"Bunker Kaliadem\", \"Alien Rock\", \"Sisa Hartaku Museum\", \"Kali Kuning Water Track\"]', 'Paket Sunrise Lava Tour Merapi dengan rute Bunker Kaliadem, Alien Rock, Sisa Hartaku Museum, and Kali Kuning Water Track.', 'Merapi Lava Tour sunrise package with Bunker Kaliadem, Alien Rock, Sisa Hartaku Museum, and Kali Kuning Water Track.', 'active', 0, '2026-06-21 10:01:39', '2026-06-23 14:15:59'),
(2, 43, 'PAKET_1', 'Paket 1', 'Package 1', 400000.00, 1, '[\"Museum Sisa Hartaku\",\"Batu Alien\",\"Track Air Kali Kuning\"]', '[\"Sisa Hartaku Museum\", \"Alien Rock\", \"Kali Kuning Water Track\"]', 'Paket Lava Tour Merapi dengan rute Sisa Hartaku Museum, Alien Rock, and Kali Kuning Water Track.', 'Merapi Lava Tour package with Sisa Hartaku Museum, Alien Rock, and Kali Kuning Water Track.', 'active', 1, '2026-06-21 10:01:39', '2026-06-23 14:15:59'),
(3, 43, 'PAKET_2A', 'Paket 2A', 'Package 2A', 450000.00, 1, '[\"Museum Sisa Hartaku\",\"Batu Alien\",\"Bunker Kaliadem\",\"Track Air Kali Kuning\"]', '[\"Sisa Hartaku Museum\", \"Alien Rock\", \"Bunker Kaliadem\", \"Kali Kuning Water Track\"]', 'Paket Lava Tour Merapi dengan rute Sisa Hartaku Museum, Alien Rock, Bunker Kaliadem, and Kali Kuning Water Track.', 'Merapi Lava Tour package with Sisa Hartaku Museum, Alien Rock, Bunker Kaliadem, and Kali Kuning Water Track.', 'active', 2, '2026-06-21 10:01:39', '2026-06-23 14:15:59'),
(4, 43, 'PAKET_2B', 'Paket 2B', 'Package 2B', 450000.00, 1, '[\"Museum Sisa Hartaku\",\"Bunker Kaliadem\",\"Petilasan Mbah Maridjan\",\"Track Air Kali Kuning\"]', '[\"Sisa Hartaku Museum\", \"Bunker Kaliadem\", \"Mbah Maridjan Memorial Site\", \"Kali Kuning Water Track\"]', 'Paket Lava Tour Merapi dengan rute Sisa Hartaku Museum, Bunker Kaliadem, Mbah Maridjan Memorial Site, and Kali Kuning Water Track.', 'Merapi Lava Tour package with Sisa Hartaku Museum, Bunker Kaliadem, Mbah Maridjan Memorial Site, and Kali Kuning Water Track.', 'active', 3, '2026-06-21 10:01:39', '2026-06-23 14:15:59'),
(5, 43, 'PAKET_2C', 'Paket 2C', 'Package 2C', 450000.00, 1, '[\"Museum Sisa Hartaku\",\"Stonehenge\",\"Bunker Kaliadem\",\"Track Air Kali Kuning\"]', '[\"Sisa Hartaku Museum\", \"Stonehenge\", \"Bunker Kaliadem\", \"Kali Kuning Water Track\"]', 'Paket Lava Tour Merapi dengan rute Sisa Hartaku Museum, Stonehenge, Bunker Kaliadem, and Kali Kuning Water Track.', 'Merapi Lava Tour package with Sisa Hartaku Museum, Stonehenge, Bunker Kaliadem, and Kali Kuning Water Track.', 'active', 4, '2026-06-21 10:01:39', '2026-06-23 14:15:59'),
(6, 43, 'PAKET_3', 'Paket 3', 'Package 3', 550000.00, 1, '[\"Museum Sisa Hartaku\",\"Batu Alien\",\"Bunker Kaliadem\",\"Petilasan Mbah Maridjan\",\"Track Air Kali Kuning\"]', '[\"Sisa Hartaku Museum\", \"Alien Rock\", \"Bunker Kaliadem\", \"Mbah Maridjan Memorial Site\", \"Kali Kuning Water Track\"]', 'Paket Lava Tour Merapi dengan rute Sisa Hartaku Museum, Alien Rock, Bunker Kaliadem, Mbah Maridjan Memorial Site, and Kali Kuning Water Track.', 'Merapi Lava Tour package with Sisa Hartaku Museum, Alien Rock, Bunker Kaliadem, Mbah Maridjan Memorial Site, and Kali Kuning Water Track.', 'active', 5, '2026-06-21 10:01:39', '2026-06-23 14:15:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reminder_logs`
--

CREATE TABLE `reminder_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `reminder_type` enum('H7','H1','HPLUS1') NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `email_to` varchar(190) NOT NULL,
  `status` enum('processing','success','failed') NOT NULL DEFAULT 'processing',
  `error_message` text DEFAULT NULL,
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `reminder_logs`
--

INSERT INTO `reminder_logs` (`id`, `booking_id`, `reminder_type`, `sent_at`, `email_to`, `status`, `error_message`, `attempts`, `created_at`, `updated_at`) VALUES
(11, 24, 'H7', '2026-06-22 11:08:42', 'anugrahags05@gmail.com', 'success', NULL, 1, '2026-06-22 04:08:38', '2026-06-22 04:08:42'),
(13, 25, 'H1', '2026-06-22 11:13:19', 'anugrahags05@gmail.com', 'success', NULL, 1, '2026-06-22 04:13:15', '2026-06-22 04:13:19'),
(16, 25, 'HPLUS1', '2026-06-24 08:00:31', 'anugrahags05@gmail.com', 'success', NULL, 1, '2026-06-24 01:00:26', '2026-06-24 01:00:31'),
(20, 33, 'H7', '2026-06-28 08:00:26', 'anamuzayanah000@gmail.com', 'success', NULL, 1, '2026-06-28 01:00:23', '2026-06-28 01:00:26'),
(21, 34, 'H7', '2026-06-28 08:00:30', 'muftidakota90@gmail.com', 'success', NULL, 1, '2026-06-28 01:00:26', '2026-06-28 01:00:30'),
(22, 45, 'H7', '2026-06-28 08:00:34', 'rahildanrls@gmail.com', 'success', NULL, 1, '2026-06-28 01:00:30', '2026-06-28 01:00:34'),
(23, 24, 'H1', '2026-06-28 08:00:37', 'anugrahags05@gmail.com', 'success', NULL, 1, '2026-06-28 01:00:34', '2026-06-28 01:00:37'),
(27, 48, 'H7', '2026-06-29 08:00:36', 'nafisahdyh@gmail.com', 'success', NULL, 1, '2026-06-29 01:00:24', '2026-06-29 01:00:36'),
(28, 49, 'H7', '2026-06-29 08:00:40', 'aliniipratiwii2709@gmail.com', 'success', NULL, 1, '2026-06-29 01:00:36', '2026-06-29 01:00:40'),
(29, 41, 'H7', '2026-06-29 08:00:43', 'thaliacharisma@gmail.com', 'success', NULL, 1, '2026-06-29 01:00:40', '2026-06-29 01:00:43'),
(35, 51, 'H7', '2026-06-30 08:00:25', 'sarahaina2023@gmail.com', 'success', NULL, 1, '2026-06-30 01:00:20', '2026-06-30 01:00:25'),
(37, 24, 'HPLUS1', '2026-06-30 08:00:29', 'anugrahags05@gmail.com', 'success', NULL, 1, '2026-06-30 01:00:25', '2026-06-30 01:00:29'),
(51, 61, 'H7', '2026-07-02 08:00:29', 'waterfalljello.ndn@gmail.com', 'success', NULL, 1, '2026-07-02 01:00:25', '2026-07-02 01:00:29'),
(53, 59, 'H1', '2026-07-02 08:00:33', 'nitasilfianah@gmail.com', 'success', NULL, 1, '2026-07-02 01:00:29', '2026-07-02 01:00:33'),
(61, 63, 'H7', '2026-07-03 08:00:28', 'marseilapuspita2707@gmail.com', 'success', NULL, 1, '2026-07-03 01:00:25', '2026-07-03 01:00:28'),
(63, 62, 'H1', '2026-07-03 08:00:32', 'nkiveileen2@gmail.com', 'success', NULL, 1, '2026-07-03 01:00:28', '2026-07-03 01:00:32'),
(65, 42, 'H7', '2026-07-04 08:00:31', 'kirananona1516@gmail.com', 'success', NULL, 1, '2026-07-04 01:00:26', '2026-07-04 01:00:31'),
(66, 50, 'H7', '2026-07-04 08:00:35', 'kirananona1516@gmail.com', 'success', NULL, 1, '2026-07-04 01:00:31', '2026-07-04 01:00:35'),
(67, 33, 'H1', '2026-07-04 08:00:39', 'anamuzayanah000@gmail.com', 'success', NULL, 1, '2026-07-04 01:00:35', '2026-07-04 01:00:39'),
(68, 34, 'H1', '2026-07-04 08:00:43', 'muftidakota90@gmail.com', 'success', NULL, 1, '2026-07-04 01:00:39', '2026-07-04 01:00:43'),
(69, 45, 'H1', '2026-07-04 08:00:47', 'rahildanrls@gmail.com', 'success', NULL, 1, '2026-07-04 01:00:43', '2026-07-04 01:00:47'),
(70, 48, 'H1', '2026-07-04 08:00:50', 'nafisahdyh@gmail.com', 'success', NULL, 1, '2026-07-04 01:00:47', '2026-07-04 01:00:50'),
(71, 49, 'H1', '2026-07-04 08:00:54', 'aliniipratiwii2709@gmail.com', 'success', NULL, 1, '2026-07-04 01:00:50', '2026-07-04 01:00:54'),
(72, 51, 'H1', NULL, 'sarahaina2023@gmail.com', 'processing', NULL, 1, '2026-07-04 01:00:54', '2026-07-04 01:00:54'),
(75, 30, 'H7', '2026-07-05 08:00:26', 'hanyamanusiabiasa04@gmail.com', 'success', NULL, 1, '2026-07-05 01:00:22', '2026-07-05 01:00:26'),
(76, 35, 'H7', '2026-07-05 08:00:30', 'kirananona1516@gmail.com', 'success', NULL, 1, '2026-07-05 01:00:26', '2026-07-05 01:00:30'),
(77, 40, 'H7', '2026-07-05 08:00:33', 'alfitraasyifa@gmail.com', 'success', NULL, 1, '2026-07-05 01:00:30', '2026-07-05 01:00:33'),
(78, 44, 'H7', '2026-07-05 08:00:36', 'sujitnoarvin@gmail.com', 'success', NULL, 1, '2026-07-05 01:00:33', '2026-07-05 01:00:36'),
(79, 54, 'H7', '2026-07-05 08:00:40', 'ainun_silvi@icloud.com', 'success', NULL, 1, '2026-07-05 01:00:36', '2026-07-05 01:00:40'),
(80, 55, 'H7', '2026-07-05 08:00:44', 'zaahroo16@gmail.com', 'success', NULL, 1, '2026-07-05 01:00:40', '2026-07-05 01:00:44'),
(81, 56, 'H7', '2026-07-05 08:00:47', 'rtnwy5705@gmail.com', 'success', NULL, 1, '2026-07-05 01:00:44', '2026-07-05 01:00:47'),
(82, 64, 'H7', '2026-07-05 08:00:52', 'inelestari51@gmail.com', 'success', NULL, 1, '2026-07-05 01:00:47', '2026-07-05 01:00:52'),
(83, 41, 'H1', NULL, 'thaliacharisma@gmail.com', 'processing', NULL, 1, '2026-07-05 01:00:52', '2026-07-05 01:00:52'),
(94, 70, 'H1', '2026-07-06 08:00:29', 'ehileritei@gmail.com', 'success', NULL, 1, '2026-07-06 01:00:25', '2026-07-06 01:00:29'),
(95, 62, 'HPLUS1', '2026-07-06 08:00:32', 'nkiveileen2@gmail.com', 'success', NULL, 1, '2026-07-06 01:00:29', '2026-07-06 01:00:32'),
(96, 66, 'HPLUS1', '2026-07-06 08:00:35', 'restiananda10@gmail.com', 'success', NULL, 1, '2026-07-06 01:00:32', '2026-07-06 01:00:35'),
(97, 67, 'HPLUS1', '2026-07-06 08:00:39', 'ibnukevran@gmail.com', 'success', NULL, 1, '2026-07-06 01:00:35', '2026-07-06 01:00:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `reviewer_name` varchar(190) NOT NULL,
  `reviewer_email` varchar(190) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `content` varchar(500) NOT NULL,
  `status` enum('approved','hidden','deleted') NOT NULL DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `trips`
--

CREATE TABLE `trips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `trip_type` enum('open','private') NOT NULL DEFAULT 'open',
  `experience_type` enum('cave','custom') NOT NULL DEFAULT 'cave',
  `status` enum('Tersedia','Penuh','Ditutup','Selesai') NOT NULL DEFAULT 'Tersedia',
  `destination_id` text DEFAULT NULL,
  `destination_en` text DEFAULT NULL,
  `description_id` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `activities_id` text DEFAULT NULL,
  `activities_en` text DEFAULT NULL,
  `facilities_id` text DEFAULT NULL,
  `facilities_en` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quota` int(11) NOT NULL DEFAULT 0,
  `slots` int(11) NOT NULL DEFAULT 0,
  `min_participants` int(11) NOT NULL DEFAULT 1,
  `max_participants` int(11) NOT NULL DEFAULT 1,
  `max_custom_pax` int(11) NOT NULL DEFAULT 0,
  `available_start_date` date DEFAULT NULL,
  `available_end_date` date DEFAULT NULL,
  `private_notes` text DEFAULT NULL,
  `private_notes_en` text DEFAULT NULL,
  `flexible_schedule` tinyint(1) NOT NULL DEFAULT 0,
  `private_booking_mode` enum('exclusive','shared') NOT NULL DEFAULT 'exclusive',
  `include_drive_link` tinyint(1) NOT NULL DEFAULT 0,
  `h7_reminder_subject` varchar(190) DEFAULT NULL,
  `h7_reminder_body` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `trips`
--

INSERT INTO `trips` (`id`, `name`, `trip_type`, `experience_type`, `status`, `destination_id`, `destination_en`, `description_id`, `description_en`, `activities_id`, `activities_en`, `facilities_id`, `facilities_en`, `price`, `quota`, `slots`, `min_participants`, `max_participants`, `max_custom_pax`, `available_start_date`, `available_end_date`, `private_notes`, `private_notes_en`, `flexible_schedule`, `private_booking_mode`, `include_drive_link`, `h7_reminder_subject`, `h7_reminder_body`, `created_at`, `updated_at`) VALUES
(33, 'Goa Ngeleng', 'open', 'cave', 'Tersedia', 'Goa Ngeleng', 'Ngeleng Cave', 'Goa Ngeleng merupakan destinasi paling menantang sekaligus goa terdalam yang kami miliki. Dengan lintasan vertical mencapai 90 meter, peserta akan merasakan sensasi rapelling turun tali yang memacu adrenalin. Setelah mencapai dasar goa, petualangan berlanjut dengan menyusuri lorong horizontal yang memperlihatkan keindahan vegetasi bawah tanah yang masih alami dan jarang tersentuh. Selain pengalaman vertical dan horizontal caving, Goa Ngeleng juga menawarkan tantangan lain, yakni scrambling untuk dapat keluar dari goa.', 'Ngeleng Cave is one of the most challenging destinations and the deepest cave offered in this program. With a vertical route reaching around 90 meters, participants will experience an adrenaline-filled rope descent. After reaching the bottom, the adventure continues through a horizontal passage with natural underground vegetation that is rarely touched. In addition to vertical and horizontal caving, Ngeleng Cave also includes a scrambling challenge to exit the cave.', '[\"Vertikal caving, eksplore goa horizontal dan scrambling.\"]', '[\"Vertical caving, horizontal cave exploration and scrambling.\"]', '[\"Alat Caving (Coverall/wearpack, Sepatu Boot, Helm, Harnes)\",\"Professional Guide bersertifikat\",\"Air mineral\",\"P3K Standart\",\"Dokumentasi Program (Foto, video & dokumentasi drone) *apabila terkendala cuaca dan drone tidak dapat terbang\",\"maka tidak ada refund\",\"Transportasi dari meeting point menuju entrance goa\"]', '[\"Caving equipment (coverall or wearpack, boots, helmet and harness)\",\"Certified professional guide\",\"Mineral water\",\"Standard first aid kit\",\"Program documentation (photos, videos and drone documentation). If weather prevents the drone from flying\",\"no refund is provided\",\"Transportation from the meeting point to the cave entrance\"]', 750000.00, 43, 8, 1, 43, 0, NULL, NULL, '', NULL, 0, 'exclusive', 1, 'Reminder – Vertical Caving Goa Ngeleng | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Vertical Caving di Goa Ngeleng bersama Maua Project. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian ganti (untuk antisipasi hujan atau kondisi basah)\n•	Hydropack atau drypack bagi yang ingin membawa barang pribadi (tidak disarankan menggunakan sling bag atau tas bahu agar tidak mengganggu aktivitas dan penggunaan tali)\n•	Sunscreen, topi, dan kacamata untuk melindungi diri dari paparan matahari\n•	Air minum tambahan, disarankan menggunakan tumbler untuk mengurangi sampah plastik\n•	Peralatan dokumentasi pribadi apabila diperlukan\n•	Obat-obatan pribadi bagi peserta yang memiliki riwayat penyakit tertentu\n\nTeknis Kegiatan Vertical Caving Goa Ngeleng\n📍Meeting Point: Kantor Kelurahan Mulusan (https://maps.app.goo.gl/gpKc7RUeRe4HZEyu6?g_st=ic)\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n\n📌 Rundown Kegiatan untuk sesi pagi\n07.30 - Sampai di meeting point\n08.00 - Menuju entrance Goa, prepare safety equipment dan wearpack\n09.00 - Simulasi & pengenalan alat\n09.30 - Vertical Caving\n11.30 - Explore Goa Horizontal\n12.00 - Kegiatan selesai, sayonara\n\n📌 Rundown Kegiatan untuk sesi siang\n13.00 - Sampai di meeting point\n14.00 - Menuju entrance Goa, prepare safety equipment dan wearpack\n14.30 - Simulasi & pengenalan alat\n15.00 - Vertical Caving\n16.30 - Explore Goa Horizontal\n17.30 - Kegiatan selesai, Sayonara\n\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\n\nSalam hangat,\nMaua Project Team 🌿', '2026-06-21 09:52:46', '2026-07-06 10:11:57'),
(34, 'Goa Sumitro', 'open', 'cave', 'Tersedia', 'Goa Sumitro', 'Sumitro Cave', 'Goa Sumitro merupakan destinasi vertical caving yang berada di kawasan Pegunungan Karst Menoreh, Kulon Progo. Goa ini memiliki kedalaman vertikal sekitar 15 meter. Setelah memasuki goa, peserta akan diajak mengeksplorasi lorong-lorong horizontal yang dihiasi berbagai formasi batuan karst yang masih alami. Keistimewaan Goa Sumitro semakin lengkap dengan adanya sungai bawah tanah yang mengalir di dalam goa, menciptakan suasana petualangan yang eksotis dan memberikan pengalaman eksplorasi bawah tanah yang berkesan bagi para pecinta alam maupun pemula yang ingin mencoba vertical caving.', 'Sumitro Cave is a vertical caving destination located in the Menoreh Karst Mountains, Kulon Progo. The cave has an approximately 15-meter vertical descent. After entering the cave, participants will explore horizontal passages decorated with natural karst formations. Its underground river adds a distinctive adventure atmosphere, making it a memorable underground exploration experience for nature lovers and beginners who want to try vertical caving.', '[\"Vertikal caving, eksplore goa horizontal dan susur sungai bawah tanah.\"]', '[\"Vertical caving, horizontal cave exploration and underground river exploration.\"]', '[\"Safety Equipment (Wearpack, Helm, Sepatu Boots, Pelampung, Headlamp)\",\"Pemandu bersertifikat (Guide)\",\"P3K\",\"Fasilitas pendopo & toilet\",\"Dokumentasi (foto & video)\",\"Teh hangat & snack tradisional di akhir sesi\"]', '[\"Safety equipment (wearpack\",\"helmet\",\"boots\",\"life jacket\",\"and headlamp)\",\"Certified guide\",\"First aid kit\",\"Pendopo and toilet facilities\",\"Photo and video documentation\",\"Warm tea and traditional snacks at the end of the session\"]', 450000.00, 13, 11, 1, 16, 0, NULL, NULL, '', NULL, 0, 'exclusive', 1, 'Reminder – Explore Caving Goa Sumitro | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Explore Caving di Goa Sumitro. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian ganti dan perlengkapan mandi\n•	Waterproof case phone bagi yang ingin membawa hp ke dalam sungai bawah tanah\n•	Peralatan dokumentasi pribadi apabila diperlukan\n•	Obat-obatan pribadi bagi peserta yang memiliki kebutuhan atau riwayat kesehatan tertentu\n\nTeknis Kegiatan Explore Caving Goa Sumitro\n📍Meeting Point: Goa Sumitro (https://maps.app.goo.gl/kbZrv9GLLXw6GmPj8?g_st=ic)\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n📌 Rundown Kegiatan\n07.30 - Registrasi peserta di Meeting point\n08.00 - Persiapan Caving\n09.00 - Explore Caving dimulai\n11.30 - Caving selesai, bebersih dan ngeteh bersama\n12.00 - Kegiatan selesai, sayonara\n\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Penambahan layanan dokumentasi tidak dapat dilakukan secara mendadak di lokasi, wajib dikonfirmasi paling lambat H-1 sebelum kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\nSalam hangat,\nMaua Project Team 🌿', '2026-06-21 09:52:46', '2026-07-06 03:54:21'),
(35, 'Goa Macan Mati', 'open', 'cave', 'Tersedia', 'Goa Macan Mati', 'Macan Mati Cave', 'Goa Macan Mati merupakan destinasi vertical caving yang menawarkan pengalaman turun goa vertical menggunakan tali dengan kedalaman mencapai 60 meter. Dikelilingi vegetasi purba yang tumbuh di dalam dolina, goa ini menyuguhkan suasana alami yang khas. Karakter goa yang terbuka serta minim eksplorasi horizontal menjadikannya lokasi yang ideal bagi pemula yang ingin mengenal dan belajar menggunakan peralatan Single Rope Technique (SRT). Dengan pendampingan instruktur berpengalaman dan standar keselamatan yang terjaga, Goa Macan Mati menjadi pilihan tepat untuk merasakan sensasi vertical caving yang seru, aman, dan penuh pengalaman baru.', 'Macan Mati Cave is a vertical caving destination that offers an experience of descending into a vertical cave using ropes, with a depth of up to 60 meters. Surrounded by ancient vegetation inside a doline, the cave presents a distinctive natural atmosphere. Its open cave character and minimal horizontal exploration make it an ideal choice for beginners who want to learn and experience Single Rope Technique equipment. With experienced instructors and strong safety standards, Macan Mati Cave is a safe and exciting option for a first vertical caving experience.', '[\"Trecking\",\"Descending dan Ascending Goa Vertikal\"]', '[\"Trekking, descending and ascending a vertical cave.\"]', '[\"Safety equipment berstandar Internasional (helm, sepatu boots, harness)\",\"Profesional guide bersertifikat\",\"Makan siang dan air mineral setelah kegiatan\"]', '[\"International-standard safety equipment (helmet, boots, and harness)\",\"Certified professional guide\",\"Lunch and mineral water after the activity\"]', 750000.00, 5, 5, 1, 5, 0, NULL, NULL, '', NULL, 0, 'exclusive', 0, 'Reminder – Vertical Caving Goa Macan Mati | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Vertical Caving di Goa Macan Mati bersama Maua Project. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian ganti (untuk antisipasi hujan atau kondisi basah)\n•	Hydropack atau drypack bagi yang ingin membawa barang pribadi (tidak disarankan menggunakan sling bag atau tas bahu agar tidak mengganggu aktivitas dan penggunaan tali)\n•	Sunscreen, topi untuk melindungi diri dari paparan matahari\n•	Air minum tambahan, disarankan menggunakan tumbler guna mengurangi sampah plastik\n•	Peralatan dokumentasi pribadi apabila diperlukan\n•	Obat-obatan pribadi bagi peserta yang memiliki riwayat penyakit tertentu\n\nTeknis Kegiatan Vertical Caving Goa Macan Mati\n📍Meeting Point: Kalisuci Cave Tubbing (https://maps.app.goo.gl/T4JKUrk1UfGhMcFA9?g_st=ic)\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n📌 Rundown Kegiatan\n07.30 - Registrasi peserta di Meeting point\n08.00 - Tracking menuju mulut goa\n08.30 - Simulasi dan pengenalan alat SRT\n09.00 - Kegiatan caving dimulai\n12.00 - Kegiatan selesai, makan siang dan sayonara\n\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\nSalam hangat,\nMaua Project Team 🌿', '2026-06-21 09:52:46', '2026-07-02 03:43:16'),
(36, 'Goa Jomblang', 'open', 'cave', 'Tersedia', 'Goa Jomblang', 'Jomblang Cave', 'Goa Jomblang menawarkan pengalaman turun ke dalam sinkhole alami sedalam sekitar 60 meter menggunakan sistem hauling. Setelah mencapai dasar goa, peserta akan diajak menyusuri hutan purba yang masih alami hingga menuju lorong goa yang terkenal dengan fenomena \"Cahaya Surga\" (Light of Heaven), yaitu sinar matahari yang menembus mulut goa dan menciptakan pemandangan yang spektakuler. Trip ini cocok bagi pemula maupun pecinta petualangan yang ingin merasakan sensasi eksplorasi goa dengan tetap didampingi oleh tim profesional dan mengutamakan standar keselamatan.', 'Jomblang Cave offers an adventure of descending into a natural sinkhole about 60 meters deep using a hauling system. After reaching the cave floor, participants will walk through a preserved ancient forest and continue to the famous passage known for the Light of Heaven, where sunlight enters through the cave opening and creates a dramatic natural view. This trip is suitable for beginners and adventure seekers who want to experience cave exploration with professional assistance and safety-focused procedures.', '[\"Vertikal caving\",\"eksplore goa horizontal\",\"take foto dan video di spot Light of Heaven.\"]', '[\"Vertical caving\",\"horizontal cave exploration\",\"and photo and video session at the Light of Heaven spot.\"]', '[\"Pemandu bersertifikat (Guide)\",\"Safety Equipment berstandar Internasional (Helm+Sepatu Boots)\",\"Durasi lama caving sekitar 1\",\"5jam-2jam dimulai pukul 09.30WIB\",\"Makan siang dan Air mineral setelah kegiatan\",\"Fasilitas pendopo & toilet\"]', '[\"Certified guide\",\"International-standard safety equipment (helmet and boots)\",\"Caving duration around 1.5 to 2 hours\",\"starting at 09:30 WIB\",\"Lunch and mineral water after the activity\",\"Pendopo and toilet facilities\"]', 500000.00, 620, 606, 1, 620, 0, NULL, NULL, '', NULL, 0, 'exclusive', 0, 'Reminder – Reguler Caving Goa Jomblang | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Reguler Caving di Goa Jomblang. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian yang nyaman untuk aktivitas outdoor (hindari pakaian yang terlalu berat, memiliki tali panjang yang menjuntai, serta penggunaan rok atau gamis)\n•	Pakaian ganti dan perlengkapan mandi (medan saat ini cukup berlumpur)\n•	Jas hujan atau ponco\n•	Hydropack/ daypack/ tas yang nyaman digunakan saat aktivitas (tidak disarankan menggunakan sling bag atau tas bahu karena dapat mengganggu pergerakan saat menggunakan tali)\n•	Peralatan dokumentasi pribadi apabila diperlukan\n•	Obat-obatan pribadi bagi peserta yang memiliki kebutuhan atau riwayat kesehatan tertentu\nTeknis Kegiatan Reguler Caving Goa Jomblang\n📍Meeting Point: Goa Jomblang (https://maps.app.goo.gl/McUYxTUKbeUUsoQ59?g_st=ic)\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n📌 Rundown Kegiatan\n06.00 – Penjemputan tamu (bagi yang menggunakan layanan penjemputan)\n09.00 - Batas maksimal kedatangan peserta di meeting point\n09.10 - Registrasi ulang\n09.30 - Kegiatan dimulai\n12.00 - Kegiatan selesai, bersih-bersih dan makan siang\n13.00 - Kegiatan berakhir, sayonara\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Penambahan layanan dokumentasi tidak dapat dilakukan secara mendadak di lokasi. Pemesanan wajib dikonfirmasi paling lambat H-1 sebelum kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\nSalam hangat,\nMaua Project Team 🌿', '2026-06-21 09:52:46', '2026-07-05 05:41:36'),
(37, 'Paddle Board', 'open', 'custom', 'Tersedia', 'Paddle Board', 'Kalisuci Paddle Board', 'Paddle Board Kalisuci menawarkan pengalaman yang unik dan berbeda dari kebanyakan destinasi di Yogyakarta, yakni menjadi salah satu spot paddle board di sungai sepanjang 750m. Kalisuci menghadirkan pemandangan air yang jernih berwarna hijau kebiruan, berpadu dengan tebing-tebing batuan karst yang menjulang di sisi kanan dan kiri sungai.', 'Kalisuci Paddle Board offers a unique experience in Yogyakarta as one of the paddle board spots along a 750-meter river route. Kalisuci features clear greenish-blue water combined with towering karst cliffs on both sides of the river, creating a refreshing and scenic outdoor activity.', '[\"Stand Up Paddle Board, susur sungai, main air, take foto dan video drone\"]', '[\"Stand up paddle board, river exploration, water play, photo and drone video session.\"]', '[\"Tiket masuk wisata\",\"Equipment\",\"Guide lokal\",\"Dokumentasi kamera & drone\",\"Teh hangat dan indomie di akhir sesi\"]', '[\"Tourism entrance ticket\",\"Equipment\",\"Local guide\",\"Camera and drone documentation\",\"Warm tea and instant noodles at the end of the session\"]', 375000.00, 36, 36, 1, 36, 0, NULL, NULL, '', NULL, 0, 'exclusive', 1, 'Reminder – Stand Up Paddle Board Kalisuci | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Stand Up Paddle Board Kalisuci. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian ganti dan perlengkapan mandi.\n•	Dry bag atau waterproof phone case apabila memiliki.\n•	Kacamata hitam\n•	Peralatan dokumentasi tambahan jika diperlukan.\n•	Obat-obatan pribadi bagi peserta yang memiliki kebutuhan atau riwayat kesehatan tertentu.\nTeknis Kegiatan Stand Up Paddle Board Kalisuci\n📍Meeting Point: Kalisuci Cave Tubbing (https://maps.app.goo.gl/T4JKUrk1UfGhMcFA9?g_st=ic)\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n\n📌 Rundown Kegiatan Sesi Pagi\n07.30 - Registrasi peserta di Meeting point\n08.00 - Persiapan kegiatan\n08.30 – Stand Up Paddle Board dimulai\n11.30 - Kegiatan selesai, bebersih dan ngeteh bersama\n12.00 - Kegiatan selesai, sayonara\n\n📌 Rundown Kegiatan Sesi Siang\n13.30 - Persiapan kegiatan\n14.00 – Stand Up Paddle Board dimulai\n15.00 - Kegiatan selesai, bebersih dan ngeteh bersama\n16.00 - Kegiatan selesai, sayonara\n\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Penambahan layanan dokumentasi tidak dapat dilakukan secara mendadak di lokasi. Pemesanan wajib dikonfirmasi paling lambat H-1 sebelum kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\nSalam hangat,\nMaua Project Team 🌿', '2026-06-21 09:52:46', '2026-07-02 03:44:07'),
(38, 'Goa Jomblang', 'private', 'cave', 'Ditutup', 'Goa Jomblang', 'Jomblang Cave', 'Goa Jomblang menawarkan pengalaman turun ke dalam sinkhole alami sedalam sekitar 60 meter menggunakan sistem hauling. Setelah mencapai dasar goa, peserta akan diajak menyusuri hutan purba yang masih alami hingga menuju lorong goa yang terkenal dengan fenomena \"Cahaya Surga\" (Light of Heaven), yaitu sinar matahari yang menembus mulut goa dan menciptakan pemandangan yang spektakuler. Trip ini cocok bagi pemula maupun pecinta petualangan yang ingin merasakan sensasi eksplorasi goa dengan tetap didampingi oleh tim profesional dan mengutamakan standar keselamatan.', 'Jomblang Cave offers an adventure of descending into a natural sinkhole about 60 meters deep using a hauling system. After reaching the cave floor, participants will walk through a preserved ancient forest and continue to the famous passage known for the Light of Heaven, where sunlight enters through the cave opening and creates a dramatic natural view. This trip is suitable for beginners and adventure seekers who want to experience cave exploration with professional assistance and safety-focused procedures.', '[\"Vertikal caving\",\"eksplore goa horizontal\",\"take foto dan video di spot Light of Heaven.\"]', '[\"Vertical caving\",\"horizontal cave exploration\",\"and photo and video session at the Light of Heaven spot.\"]', '[\"Pemandu bersertifikat (Guide)\",\"Safety Equipment berstandar Internasional (Helm+Sepatu Boots)\",\"Durasi lama caving sekitar 1\",\"5jam-2jam dimulai pukul 09.30WIB\",\"Makan siang dan Air mineral setelah kegiatan\",\"Fasilitas pendopo & toilet\"]', '[\"Certified guide\",\"International-standard safety equipment (helmet and boots)\",\"Caving duration around 1.5 to 2 hours\",\"starting at 09:30 WIB\",\"Lunch and mineral water after the activity\",\"Pendopo and toilet facilities\"]', 500000.00, 20, 20, 1, 20, 1, '2026-07-01', '2026-07-31', 'Harga private Goa Jomblang perlu dikonfirmasi admin terlebih dahulu.', 'Private trip availability is open throughout July. Please select the available date and session before checkout.', 1, 'shared', 0, 'Reminder – Reguler Caving Goa Jomblang | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Reguler Caving di Goa Jomblang. Tidak terasa, kegiatan kita sudah memasuki {sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian yang nyaman untuk aktivitas outdoor (hindari pakaian yang terlalu berat, memiliki tali panjang yang menjuntai, serta penggunaan rok atau gamis)\n•	Pakaian ganti dan perlengkapan mandi (medan saat ini cukup berlumpur)\n•	Jas hujan atau ponco\n•	Hydropack/ daypack/ tas yang nyaman digunakan saat aktivitas (tidak disarankan menggunakan sling bag atau tas bahu karena dapat mengganggu pergerakan saat menggunakan tali)\n•	Peralatan dokumentasi pribadi apabila diperlukan\n•	Obat-obatan pribadi bagi peserta yang memiliki kebutuhan atau riwayat kesehatan tertentu\n\nTeknis Kegiatan Reguler Caving Goa Jomblang\n📍Meeting Point: Goa Jomblang (https://maps.app.goo.gl/McUYxTUKbeUUsoQ59?g_st=ic)\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n\n📌 Rundown Kegiatan\n06.00 – Penjemputan tamu (bagi yang menggunakan layanan penjemputan)\n09.00 - Batas maksimal kedatangan peserta di meeting point\n09.10 - Registrasi ulang\n09.30 - Kegiatan dimulai\n12.00 - Kegiatan selesai, bersih-bersih dan makan siang\n13.00 - Kegiatan berakhir, sayonara\n\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Penambahan layanan dokumentasi tidak dapat dilakukan secara mendadak di lokasi. Pemesanan wajib dikonfirmasi paling lambat H-1 sebelum kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\nSalam hangat,\nMaua Project Team 🌿', '2026-06-21 09:52:46', '2026-06-24 10:28:09'),
(39, 'Goa Macan Mati', 'private', 'cave', 'Tersedia', 'Goa Macan Mati', 'Macan Mati Cave', 'Goa Macan Mati merupakan destinasi vertical caving yang menawarkan pengalaman turun goa vertical menggunakan tali dengan kedalaman mencapai 60 meter. Dikelilingi vegetasi purba yang tumbuh di dalam dolina, goa ini menyuguhkan suasana alami yang khas. Karakter goa yang terbuka serta minim eksplorasi horizontal menjadikannya lokasi yang ideal bagi pemula yang ingin mengenal dan belajar menggunakan peralatan Single Rope Technique (SRT). Dengan pendampingan instruktur berpengalaman dan standar keselamatan yang terjaga, Goa Macan Mati menjadi pilihan tepat untuk merasakan sensasi vertical caving yang seru, aman, dan penuh pengalaman baru.', 'Macan Mati Cave is a vertical caving destination that offers an experience of descending into a vertical cave using ropes, with a depth of up to 60 meters. Surrounded by ancient vegetation inside a doline, the cave presents a distinctive natural atmosphere. Its open cave character and minimal horizontal exploration make it an ideal choice for beginners who want to learn and experience Single Rope Technique equipment. With experienced instructors and strong safety standards, Macan Mati Cave is a safe and exciting option for a first vertical caving experience.', '[\"Trecking\",\"Descending dan Ascending Goa Vertikal\"]', '[\"Trekking\",\"descending\",\"and ascending a vertical cave.\"]', '[\"Safety equipment berstandar Internasional (helm\",\"sepatu boots\",\"harness)\",\"Profesional guide bersertifikat\",\"Makan siang dan air mineral setelah kegiatan\"]', '[\"International-standard safety equipment (helmet\",\"boots\",\"and harness)\",\"Certified professional guide\",\"Lunch and mineral water after the activity\"]', 750000.00, 5, 5, 1, 5, 5, '2026-07-01', '2026-07-31', '', 'Private trip availability is open throughout July with one morning session.', 1, 'exclusive', 0, 'Reminder – Vertical Caving Goa Macan Mati | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Vertical Caving di Goa Macan Mati bersama Maua Project. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian ganti (untuk antisipasi hujan atau kondisi basah)\n•	Hydropack atau drypack bagi yang ingin membawa barang pribadi (tidak disarankan menggunakan sling bag atau tas bahu agar tidak mengganggu aktivitas dan penggunaan tali)\n•	Sunscreen, topi untuk melindungi diri dari paparan matahari\n•	Air minum tambahan, disarankan menggunakan tumbler guna mengurangi sampah plastik\n•	Peralatan dokumentasi pribadi apabila diperlukan\n•	Obat-obatan pribadi bagi peserta yang memiliki riwayat penyakit tertentu\n\nTeknis Kegiatan Vertical Caving Goa Macan Mati\n📍Meeting Point: Kalisuci Cave Tubbing (https://maps.app.goo.gl/T4JKUrk1UfGhMcFA9?g_st=ic)\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n📌 Rundown Kegiatan\n07.30 - Registrasi peserta di Meeting point\n08.00 - Tracking menuju mulut goa\n08.30 - Simulasi dan pengenalan alat SRT\n09.00 - Kegiatan caving dimulai\n12.00 - Kegiatan selesai, makan siang dan sayonara\n\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\nSalam hangat,\nMaua Project Team 🌿', '2026-06-21 09:52:46', '2026-06-28 07:01:21'),
(40, 'Goa Sumitro', 'private', 'cave', 'Tersedia', 'Goa Sumitro', 'Sumitro Cave', 'Goa Sumitro merupakan destinasi vertical caving yang berada di kawasan Pegunungan Karst Menoreh, Kulon Progo. Goa ini memiliki kedalaman vertikal sekitar 15 meter. Setelah memasuki goa, peserta akan diajak mengeksplorasi lorong-lorong horizontal yang dihiasi berbagai formasi batuan karst yang masih alami. Keistimewaan Goa Sumitro semakin lengkap dengan adanya sungai bawah tanah yang mengalir di dalam goa, menciptakan suasana petualangan yang eksotis dan memberikan pengalaman eksplorasi bawah tanah yang berkesan bagi para pecinta alam maupun pemula yang ingin mencoba vertical caving.', 'Sumitro Cave is a vertical caving destination located in the Menoreh Karst Mountains, Kulon Progo. The cave has an approximately 15-meter vertical descent. After entering the cave, participants will explore horizontal passages decorated with natural karst formations. Its underground river adds a distinctive adventure atmosphere, making it a memorable underground exploration experience for nature lovers and beginners who want to try vertical caving.', '[\"Vertikal caving\",\"eksplore goa horizontal dan susur sungai bawah tanah.\"]', '[\"Vertical caving\",\"horizontal cave exploration\",\"and underground river exploration.\"]', '[\"Safety Equipment (Wearpack+Helm+Sepatu Boots+Pelampung+Headlamp)\",\"Pemandu bersertifikat (Guide)\",\"P3K\",\"Fasilitas pendopo & toilet\",\"Dokumentasi (foto & video)\",\"Teh hangat & snack tradisional di akhir sesi\"]', '[\"Safety equipment (wearpack\",\"helmet\",\"boots\",\"life jacket\",\"and headlamp)\",\"Certified guide\",\"First aid kit\",\"Pendopo and toilet facilities\",\"Photo and video documentation\",\"Warm tea and traditional snacks at the end of the session\"]', 450000.00, 8, 8, 1, 8, 8, '2026-07-01', '2026-07-31', '', 'Private trip availability is open throughout July with morning and afternoon sessions.', 1, 'exclusive', 1, 'Reminder – Explore Caving Goa Sumitro | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Explore Caving di Goa Sumitro. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian ganti dan perlengkapan mandi\n•	Waterproof case phone bagi yang ingin membawa hp ke dalam sungai bawah tanah\n•	Peralatan dokumentasi pribadi apabila diperlukan\n•	Obat-obatan pribadi bagi peserta yang memiliki kebutuhan atau riwayat kesehatan tertentu\nTeknis Kegiatan Explore Caving Goa Sumitro\n📍Meeting Point: Goa Sumitro (https://maps.app.goo.gl/kbZrv9GLLXw6GmPj8?g_st=ic)\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n📌 Rundown Kegiatan\n07.30 - Registrasi peserta di Meeting point\n08.00 - Persiapan Caving\n09.00 - Explore Caving dimulai\n11.30 - Caving selesai, bebersih dan ngeteh bersama\n12.00 - Kegiatan selesai, sayonara\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Penambahan layanan dokumentasi tidak dapat dilakukan secara mendadak di lokasi. Pemesanan wajib dikonfirmasi paling lambat H-1 sebelum kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\nSalam hangat,\nMaua Project Team 🌿', '2026-06-21 09:52:46', '2026-07-02 03:44:21'),
(41, 'Goa Ngeleng', 'private', 'cave', 'Ditutup', 'Goa Ngeleng', 'Ngeleng Cave', 'Goa Ngeleng merupakan destinasi paling menantang sekaligus goa terdalam yang kami miliki. Dengan lintasan vertical mencapai 90 meter, peserta akan merasakan sensasi rapelling turun tali yang memacu adrenalin. Setelah mencapai dasar goa, petualangan berlanjut dengan menyusuri lorong horizontal yang memperlihatkan keindahan vegetasi bawah tanah yang masih alami dan jarang tersentuh. Selain pengalaman vertical dan horizontal caving, Goa Ngeleng juga menawarkan tantangan lain, yakni scrambling untuk dapat keluar dari goa.\r\n\r\nCatatan: Untuk private trip Goa Ngeleng, peserta diharuskan tanya admin terlebih dahulu sebelum checkout.', 'Ngeleng Cave is one of the most challenging destinations and the deepest cave offered in this program. With a vertical route reaching around 90 meters, participants will experience an adrenaline-filled rope descent. After reaching the bottom, the adventure continues through a horizontal passage with natural underground vegetation that is rarely touched. In addition to vertical and horizontal caving, Ngeleng Cave also includes a scrambling challenge to exit the cave.', '[\"Vertikal caving\",\"eksplore goa horizontal dan scrambling.\"]', '[\"Vertical caving\",\"horizontal cave exploration\",\"and scrambling.\"]', '[\"Alat Caving (Coverall/wearpack\",\"Sepatu Boot\",\"Helm\",\"Harnes)\",\"Professional Guide bersertifikat\",\"Air mineral\",\"P3K Standart\",\"Dokumentasi Program (Foto\",\"video & dokumentasi drone) *apabila terkendala cuaca dan drone tidak dapat terbang\",\"maka tidak ada refund\",\"Transportasi dari meeting point menuju entrance goa\"]', '[\"Caving equipment (coverall or wearpack\",\"boots\",\"helmet\",\"and harness)\",\"Certified professional guide\",\"Mineral water\",\"Standard first aid kit\",\"Program documentation (photos\",\"videos\",\"and drone documentation). If weather prevents the drone from flying\",\"no refund is provided\",\"Transportation from the meeting point to the cave entrance\"]', 750000.00, 10, 10, 5, 10, 10, '2026-07-01', '2026-07-31', 'Untuk pemesanan Private Trip Goa Ngeleng, peserta diharuskan tanya admin terlebih dahulu sebelum checkout.', 'Private trip availability is open throughout July with morning and afternoon sessions. Please contact the admin first before booking this trip because the route and operational conditions need to be confirmed.', 1, 'exclusive', 0, 'Reminder – Vertical Caving Goa Ngeleng | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Vertical Caving di Goa Ngeleng bersama Maua Project. Tidak terasa, kegiatan kita sudah memasuki {sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian ganti (untuk antisipasi hujan atau kondisi basah)\n•	Hydropack atau drypack bagi yang ingin membawa barang pribadi (tidak disarankan menggunakan sling bag atau tas bahu agar tidak mengganggu aktivitas dan penggunaan tali)\n•	Sunscreen, topi, dan kacamata untuk melindungi diri dari paparan matahari\n•	Air minum tambahan, disarankan menggunakan tumbler guna mengurangi sampah plastik\n•	Peralatan dokumentasi pribadi apabila diperlukan\n•	Obat-obatan pribadi bagi peserta yang memiliki riwayat penyakit tertentu\n\nTeknis Kegiatan Vertical Caving Goa Ngeleng\n📍Meeting Point: Kantor Kelurahan Mulusan (https://maps.app.goo.gl/gpKc7RUeRe4HZEyu6?g_st=ic)\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n\n📌 Rundown Kegiatan untuk sesi pagi\n07.30 - Registrasi peserta di Meeting point\n08.00 - Persiapan Caving\n09.00 - Vertical Caving\n11.30 - Selesai, kembali ke meeting point\n12.00 - Kegiatan selesai, sayonara\n\n📌 Rundown Kegiatan untuk sesi siang\n13.30 - Sampai di meeting point\n14.00 - Menuju entrance Goa, prepare safety equipment dan wearpack\n14.30 - Simulasi & pengenalan alat\n15.00 - Vertical Caving 16.30 - Explore Goa Horizontal\n17.30 - Kegiatan selesai, Sayonara\n\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\n\nSalam hangat,\nMaua Project Team 🌿', '2026-06-21 09:52:46', '2026-06-25 03:09:56'),
(42, 'Paddle Board', 'private', 'custom', 'Tersedia', 'Paddle Board', 'Kalisuci Paddle Board', 'Paddle Board Kalisuci menawarkan pengalaman yang unik dan berbeda dari kebanyakan destinasi di Yogyakarta, yakni menjadi salah satu spot paddle board di sungai sepanjang 750m. Kalisuci menghadirkan pemandangan air yang jernih berwarna hijau kebiruan, berpadu dengan tebing-tebing batuan karst yang menjulang di sisi kanan dan kiri sungai.', 'Kalisuci Paddle Board offers a unique experience in Yogyakarta as one of the paddle board spots along a 750-meter river route. Kalisuci features clear greenish-blue water combined with towering karst cliffs on both sides of the river, creating a refreshing and scenic outdoor activity.', '[\"Stand Up Paddle Board, susur sungai, main air, take foto dan video drone\"]', '[\"Stand up paddle board, river exploration, water play and photo and drone video session.\"]', '[\"Tiket masuk wisata\",\"Equipment\",\"Guide lokal\",\"Dokumentasi kamera & drone\",\"Teh hangat dan indomie di akhir sesi\"]', '[\"Tourism entrance ticket\",\"Equipment\",\"Local guide\",\"Camera and drone documentation\",\"Warm tea and instant noodles at the end of the session\"]', 300000.00, 6, 6, 2, 6, 2, '2026-07-01', '2026-07-31', '', 'Private trip availability is open throughout July with morning and afternoon sessions.', 1, 'shared', 0, 'Reminder – Stand Up Paddle Board Kalisuci | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Stand Up Paddle Board Kalisuci. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian ganti dan perlengkapan mandi.\n•	Dry bag atau waterproof phone case apabila memiliki.\n•	Kacamata hitam\n•	Peralatan dokumentasi tambahan jika diperlukan.\n•	Obat-obatan pribadi bagi peserta yang memiliki kebutuhan atau riwayat kesehatan tertentu.\nTeknis Kegiatan Stand Up Paddle Board Kalisuci\n📍Meeting Point: Kalisuci Cave Tubbing (https://maps.app.goo.gl/T4JKUrk1UfGhMcFA9?g_st=ic)\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n\n📌 Rundown Kegiatan Sesi Pagi\n07.30 - Registrasi peserta di Meeting point\n08.00 - Persiapan kegiatan\n08.30 – Stand Up Paddle Board dimulai\n11.30 - Kegiatan selesai, bebersih dan ngeteh bersama\n12.00 - Kegiatan selesai, sayonara\n\n📌 Rundown Kegiatan Sesi Siang\n13.30 - Persiapan kegiatan\n14.00 – Stand Up Paddle Board dimulai\n15.00 - Kegiatan selesai, bebersih dan ngeteh bersama\n16.00 - Kegiatan selesai, sayonara\n\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Penambahan layanan dokumentasi tidak dapat dilakukan secara mendadak di lokasi. Pemesanan wajib dikonfirmasi paling lambat H-1 sebelum kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\nSalam hangat,\nMaua Project Team 🌿', '2026-06-21 09:52:46', '2026-07-01 05:29:11'),
(43, 'Lava Tour Merapi', 'private', 'custom', 'Tersedia', 'Lava Tour Merapi', 'Merapi Lava Tour', 'Lava Tour Merapi menawarkan petualangan menggunakan jeep menjelajahi kawasan lereng Gunung Merapi sambil menyaksikan jejak dahsyat erupsi yang pernah terjadi. Perjalanan akan melewati berbagai medan off-road seru, mulai dari jalan berbatu, sungai, hingga kawasan bekas aliran lava, dengan latar pemandangan megah Gunung Merapi yang menjulang di kejauhan. Aktivitas ini cocok bagi peserta yang ingin menikmati kombinasi antara wisata alam, sejarah, dan petualangan dalam satu pengalaman yang tak terlupakan.', 'Merapi Lava Tour offers an off-road jeep adventure around the slopes of Mount Merapi while exploring traces of past volcanic eruptions. The route passes rocky roads, river areas, and former lava flow zones, with Mount Merapi as the main natural backdrop. This activity is suitable for participants who want to enjoy nature, history, and adventure in one memorable experience.', '[\"Tour Jeep\",\"kunjung museum\",\"menikmati view gunung Merapi\",\"take foto dan video\"]', '[\"Jeep tour\",\"museum visit\",\"enjoying Mount Merapi views\",\"and photo and video session.\"]', '[\"Driver dan Lokal Guide\",\"Unit Jeep (include bbm)\",\"Helm\",\"Tiket masuk wisata\"]', '[\"Driver and local guide\",\"Jeep unit including fuel\",\"Helmet\",\"Tourism entrance ticket\"]', 400000.00, 4, 4, 1, 4, 4, '2026-07-01', '2026-07-31', 'Harga Lava Tour Merapi berdasarkan pilihan paket. Silakan pilih tanggal, sesi keberangkatan, dan paket yang tersedia.', 'Price depends on the selected Lava Tour package. Please choose the date, departure session, and package before checkout.', 1, 'exclusive', 0, NULL, NULL, '2026-06-21 09:52:46', '2026-06-28 07:01:48'),
(44, 'Cave Tubbing - Kalisuci', 'private', 'custom', 'Tersedia', 'Cave Tubbing - Kalisuci', 'Kalisuci Cave Tubing', 'Kalisuci Cave Tubing mengajak peserta untuk menyusuri sungai bawah tanah menggunakan ban pelampung. Selama perjalanan, peserta akan menikmati sensasi mengapung mengikuti aliran sungai yang jernih sambil melewati lorong-lorong goa alami yang terbentuk dari kawasan karst Gunungsewu. Cahaya matahari yang masuk melalui celah-celah goa menciptakan pemandangan yang menakjubkan, sementara dinding batuan karst yang menjulang di sepanjang jalur menambah kesan eksotis dan memukau. Selain menyusuri sungai, peserta juga akan merasakan pengalaman berenang, melompat dari titik-titik tertentu, serta menikmati suasana alam yang masih asri dan alami.', 'Kalisuci Cave Tubing invites participants to explore an underground river using an inflatable tube. Along the route, participants will float along clear river water while passing through natural cave passages formed in the Gunungsewu karst area. Sunlight entering through cave openings creates beautiful scenery, while the surrounding karst walls add an exotic natural atmosphere. The activity may also include swimming, jumping from selected spots, and enjoying a fresh outdoor setting.', '[\"Cave Tubbing, susur sungai, main air, take foto dan video\"]', '[\"Cave tubing, river exploration, water play and photo and video session.\"]', '[\"Safety Equipment (Helm\",\"Pelampung\",\"Deker)\",\"Pemandu Lokal (3 orang/group)\",\"Pendopo & toilet\",\"Loker\",\"Indomie dan the hangat di akhir sesi\"]', '[\"Safety equipment (helmet\",\"life jacket\",\"and protective pads)\",\"Local guides\",\"3 guides per group\",\"Pendopo and toilet facilities\",\"Locker\",\"Instant noodles and warm tea at the end of the session\"]', 120000.00, 100, 100, 2, 100, 1, '2026-07-01', '2026-07-31', '', 'Private trip availability is open throughout July.', 1, 'shared', 0, NULL, NULL, '2026-06-21 09:52:46', '2026-06-28 07:01:55'),
(45, 'Goa Ngeleng', 'private', 'cave', 'Tersedia', 'Goa Ngeleng', 'Ngeleng Cave', 'Goa Ngeleng merupakan destinasi paling menantang sekaligus goa terdalam yang kami miliki. Dengan lintasan vertical mencapai 90 meter, peserta akan merasakan sensasi rapelling turun tali yang memacu adrenalin. Setelah mencapai dasar goa, petualangan berlanjut dengan menyusuri lorong horizontal yang memperlihatkan keindahan vegetasi bawah tanah yang masih alami dan jarang tersentuh. Selain pengalaman vertical dan horizontal caving, Goa Ngeleng juga menawarkan tantangan lain, yakni scrambling untuk dapat keluar dari goa.', 'Ngeleng Cave is our most challenging destination and the deepest cave we offer. With a vertical descent of approximately 90 meters, participants will experience the thrill of adrenaline-pumping rope rappelling into the cave. Once at the bottom, the adventure continues through a horizontal cave passage, where you can admire the untouched beauty of its underground vegetation and unique natural formations. Beyond the excitement of both vertical and horizontal caving, Ngeleng Cave also presents an additional challenge: scrambling to make your way back out of the cave, making it a truly rewarding adventure for experienced thrill seekers.', '[\"Vertikal caving, eksplore goa horizontal dan scrambling.\"]', '[\"Vertikal caving, Cave eksplore horizontal and scrambling.\"]', '[\"Alat Caving (Coverall/wearpack, Sepatu Boot, Helm, Harnes)\",\"Professional Guide bersertifikat\",\"Air mineral\",\"P3K Standart\",\"⁠Dokumentasi Program (Foto, video & dokumentasi drone) *apabila terkendala cuaca dan drone tidak dapat terbang, maka tidak ada refund\",\"Transportasi dari meeting point menuju entrance goa\"]', '[\"Complete Caving Equipment (Coverall, Safety Boots, Helmet, and Harness)\",\"Certified Professional Caving Guide\",\"Mineral Water\",\"Standard First Aid Kit\",\"Adventure Documentation Package (Photos, Videos, and Drone Footage). If weather conditions prevent drone operation, no refund will be provided.\",\"Transportation from the Meeting Point to the Cave Entrance\"]', 850000.00, 10, 10, 4, 10, 5, '2026-07-01', '2026-08-31', '', NULL, 1, 'exclusive', 1, 'Reminder – Vertical Caving Goa Ngeleng | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Vertical Caving di Goa Ngeleng bersama Maua Project. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian ganti (untuk antisipasi hujan atau kondisi basah)\n•	Hydropack atau drypack bagi yang ingin membawa barang pribadi (tidak disarankan menggunakan sling bag atau tas bahu agar tidak mengganggu aktivitas dan penggunaan tali)\n•	Sunscreen, topi, dan kacamata untuk melindungi diri dari paparan matahari\n•	Air minum tambahan, disarankan menggunakan tumbler guna mengurangi sampah plastik\n•	Peralatan dokumentasi pribadi apabila diperlukan\n•	Obat-obatan pribadi bagi peserta yang memiliki riwayat penyakit tertentu\n\nTeknis Kegiatan Vertical Caving Goa Ngeleng\n📍Meeting Point: Kantor Kelurahan Mulusan (https://maps.app.goo.gl/gpKc7RUeRe4HZEyu6?g_st=ic)\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n\n📌 Rundown Kegiatan Sesi Pagi\n07.30 - Sampai di meeting point\n08.00 - Menuju entrance Goa, prepare safety equipment dan wearpack\n08.30 - Simulasi & pengenalan alat\n09.00 - Vertical Caving, Explore Goa Horizontal\n12.00 - Kegiatan selesai, sayonara\n\n📌 Rundown Kegiatan Sesi Siang\n13.30 - Sampai di meeting point\n14.00 - Menuju entrance Goa, prepare safety equipment dan wearpack\n14.30 - Simulasi & pengenalan alat\n15.00 - Vertical Caving, Explore Goa Horizontal\n17.30 - Kegiatan selesai, Sayonara\n\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\nSalam hangat,\nMaua Project Team 🌿', '2026-06-28 06:53:23', '2026-07-02 03:44:37'),
(46, '1 Day Trip Goa Ngeleng & Jetski', 'private', 'cave', 'Tersedia', 'Goa Ngeleng & Jetski', 'Ngeleng Cave & Jetski', 'Private Trip Goa Ngeleng & Jetski', 'Private Trip Ngeleng Cave & Jetski', '[\"Goa Ngeleng: Vertikal caving, eksplore goa horizontal dan scrambling.\",\"Jetski: Jetski, main air, take foto dan video drone.\"]', '[\"Ngeleng Cave: Vertical caving, horizontal cave exploration, and scrambling.\",\"Jetski: Jetskiing, water activities, and taking photos and drone videos.\"]', '[\"Goa Ngeleng:\",\"Alat Caving (Coverall/wearpack, Sepatu Boot, Helm, Harnes)\",\"Professional Guide bersertifikat\",\"Air mineral\",\"P3K Standart\",\"Dokumentasi Program (Foto, video & dokumentasi drone) *apabila terkendala cuaca dan drone tidak dapat terbang\",\"maka tidak ada refund\",\"Transportasi dari meeting point menuju entrance goa\",\"Jetski:\",\"Safety Equipment\",\"Jetski masing masing peserta\",\"Dokumentasi foto dan drone\",\"Editing video\"]', '[\"Ngeleng Cave:\",\"Caving gear (coveralls, boots, helmet, harness)\",\"Certified professional guide\",\"Drinking water\",\"Standard first aid kit\",\"Activity documentation (photos, video, and drone footage) *Note: No refunds will be issued if weather conditions prevent drone flight\",\"Transportation from the meeting point to the cave entrance\",\"Jetski:\",\"Safety equipment\",\"Individual jetski for each participant\",\"Photo and drone documentation\",\"Video editing\"]', 1600000.00, 7, 7, 4, 7, 4, '2026-07-01', '2026-07-31', '', NULL, 1, 'exclusive', 1, 'Reminder – Vertical Caving Goa Ngeleng | {tanggal_trip}', 'Hi, Sobat Maua 👋\nTerima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan bersama Maua Project. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻\n\nUntuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:\n•	Pakaian ganti (untuk antisipasi hujan atau kondisi basah)\n•	Hydropack atau drypack bagi yang ingin membawa barang pribadi (tidak disarankan menggunakan sling bag atau tas bahu agar tidak mengganggu aktivitas dan penggunaan tali)\n•	Sunscreen, topi, dan kacamata untuk melindungi diri dari paparan matahari\n•	Air minum tambahan, disarankan menggunakan tumbler untuk mengurangi sampah plastik\n•	Peralatan dokumentasi pribadi apabila diperlukan\n•	Obat-obatan pribadi bagi peserta yang memiliki riwayat penyakit tertentu\n\nTeknis Kegiatan\n📆 Tanggal: {tanggal_trip}\n🕑 Waktu: {jam_trip}\n\n📌 Rundown Kegiatan\n06.00 - Penjemputan Driver\n07.30 - Sampai di meeting point\n08.00 - Menuju entrance Goa, prepare safety equipment dan wearpack\n09.00 - Simulasi & pengenalan alat\n09.30 - Vertical Caving\n11.30 - Explore Goa Horizontal\n12.00 - Kegiatan caving selesai, lanjut ke lokasi Jetski\n14.00 - Sampai di Jogja Jetski\n14.15 - Prepare dan latihan sebelum kegiatan\n14.30 - Kegiatan Jetski\n15.00 - Kegiatan selesai, kembali ke lokasi penjemputan, sayonara\n\n📢 Catatan Penting\n•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.\n•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.\n•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.\n•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.\n\nSalam hangat,\nMaua Project Team 🌿', '2026-07-06 10:15:53', '2026-07-06 11:17:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `trip_addons`
--

CREATE TABLE `trip_addons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `max_participants_per_unit` int(10) UNSIGNED DEFAULT NULL,
  `worker_action` enum('drive_link','none') NOT NULL DEFAULT 'none',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `trip_addons`
--

INSERT INTO `trip_addons` (`id`, `trip_id`, `name`, `price`, `max_participants_per_unit`, `worker_action`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(175, 33, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 0, '2026-06-21 09:52:46', '2026-07-02 05:43:36'),
(176, 33, 'Transportasi B - Mobil maksimal 3 orang', 650000.00, NULL, 'none', 'active', 1, '2026-06-21 09:52:46', '2026-06-21 10:09:27'),
(177, 33, 'Camera Insta360', 250000.00, NULL, 'drive_link', 'active', 2, '2026-06-21 09:52:46', '2026-06-28 05:37:18'),
(178, 33, 'Baterai Drone 20 menit', 150000.00, NULL, 'drive_link', 'active', 3, '2026-06-21 09:52:46', '2026-06-28 05:37:18'),
(179, 33, 'Ojek Trip Goa Ngeleng Only', 200000.00, NULL, 'none', 'active', 4, '2026-06-21 09:52:46', '2026-06-21 10:09:27'),
(180, 33, 'Ojek Trip Fullday', 400000.00, NULL, 'none', 'active', 5, '2026-06-21 09:52:46', '2026-06-21 10:09:27'),
(181, 34, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 0, '2026-06-21 09:52:46', '2026-06-21 10:41:04'),
(182, 34, 'Transportasi B - Mobil maksimal 3 orang', 650000.00, 3, 'none', 'active', 1, '2026-06-21 09:52:46', '2026-06-24 15:19:18'),
(183, 34, 'Camera Insta360', 250000.00, NULL, 'drive_link', 'active', 2, '2026-06-21 09:52:46', '2026-07-02 03:42:53'),
(184, 34, 'Ojek Trip Goa Sumitro Only', 200000.00, NULL, 'none', 'active', 3, '2026-06-21 09:52:46', '2026-06-21 10:41:04'),
(185, 34, 'Ojek Trip Fullday', 400000.00, NULL, 'none', 'active', 4, '2026-06-21 09:52:46', '2026-06-21 10:41:04'),
(186, 35, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 0, '2026-06-21 09:52:46', '2026-06-21 10:48:01'),
(187, 35, 'Transportasi B - Mobil maksimal 3 orang', 650000.00, NULL, 'none', 'active', 1, '2026-06-21 09:52:46', '2026-06-21 10:48:01'),
(188, 35, 'Dokumentasi Foto Camera + Video iPhone', 700000.00, NULL, 'drive_link', 'active', 2, '2026-06-21 09:52:46', '2026-06-21 10:48:01'),
(189, 35, 'Dokumentasi Foto + Video iPhone', 600000.00, NULL, 'drive_link', 'active', 3, '2026-06-21 09:52:46', '2026-06-21 10:48:01'),
(190, 35, 'Dokumentasi Foto Camera', 500000.00, NULL, 'drive_link', 'active', 4, '2026-06-21 09:52:46', '2026-06-21 10:48:01'),
(191, 35, 'Camera Insta360', 250000.00, NULL, 'none', 'active', 5, '2026-06-21 09:52:46', '2026-06-21 10:48:01'),
(192, 35, 'Ojek Trip Goa Macan Mati Only', 200000.00, NULL, 'none', 'active', 6, '2026-06-21 09:52:46', '2026-06-21 10:48:01'),
(193, 35, 'Ojek Trip Fullday', 400000.00, NULL, 'none', 'active', 7, '2026-06-21 09:52:46', '2026-06-21 10:48:01'),
(194, 36, 'Dokumentasi Foto Camera + Video iPhone', 950000.00, NULL, 'drive_link', 'active', 0, '2026-06-21 09:52:46', '2026-06-21 10:59:05'),
(195, 36, 'Dokumentasi Foto Camera', 750000.00, NULL, 'drive_link', 'active', 1, '2026-06-21 09:52:46', '2026-06-21 10:59:05'),
(196, 36, 'Dokumentasi Foto + Video iPhone', 850000.00, NULL, 'drive_link', 'active', 2, '2026-06-21 09:52:46', '2026-06-21 10:59:05'),
(197, 36, 'Camera Insta360', 250000.00, NULL, 'drive_link', 'active', 3, '2026-06-21 09:52:46', '2026-07-05 05:41:36'),
(198, 36, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 4, '2026-06-21 09:52:46', '2026-06-21 10:59:05'),
(199, 36, 'Ojek Trip Jomblang Only', 200000.00, NULL, 'none', 'active', 5, '2026-06-21 09:52:46', '2026-06-21 10:59:05'),
(200, 36, 'Ojek Trip Fullday', 400000.00, NULL, 'none', 'active', 6, '2026-06-21 09:52:46', '2026-06-21 10:59:05'),
(201, 37, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 0, '2026-06-21 09:52:46', '2026-06-21 11:07:50'),
(202, 37, 'Transportasi B - Mobil maksimal 3 orang', 650000.00, NULL, 'none', 'active', 1, '2026-06-21 09:52:46', '2026-06-21 11:07:50'),
(203, 37, 'Baterai Drone 20 menit', 150000.00, NULL, 'none', 'active', 2, '2026-06-21 09:52:46', '2026-06-21 11:07:50'),
(204, 37, 'Ojek Trip Goa Ngeleng Only', 200000.00, NULL, 'none', 'active', 3, '2026-06-21 09:52:46', '2026-06-21 11:07:50'),
(205, 37, 'Ojek Trip Fullday', 400000.00, NULL, 'none', 'active', 4, '2026-06-21 09:52:46', '2026-06-21 11:07:50'),
(206, 38, 'Dokumentasi Foto Camera + Video iPhone', 950000.00, NULL, 'drive_link', 'active', 0, '2026-06-21 09:52:46', '2026-06-21 11:00:42'),
(207, 38, 'Dokumentasi Foto Camera', 750000.00, NULL, 'drive_link', 'active', 1, '2026-06-21 09:52:46', '2026-06-21 11:00:42'),
(208, 38, 'Dokumentasi Foto + Video iPhone', 850000.00, NULL, 'drive_link', 'active', 2, '2026-06-21 09:52:46', '2026-06-21 11:00:42'),
(209, 38, 'Camera Insta360', 250000.00, NULL, 'none', 'active', 3, '2026-06-21 09:52:46', '2026-06-21 11:00:42'),
(210, 38, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 4, '2026-06-21 09:52:46', '2026-06-21 11:00:42'),
(211, 38, 'Ojek Trip Jomblang Only', 200000.00, NULL, 'none', 'active', 5, '2026-06-21 09:52:46', '2026-06-21 11:00:42'),
(212, 38, 'Ojek Trip Fullday', 400000.00, NULL, 'none', 'active', 6, '2026-06-21 09:52:46', '2026-06-21 11:00:42'),
(213, 39, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 0, '2026-06-21 09:52:46', '2026-06-21 10:48:50'),
(214, 39, 'Transportasi B - Mobil maksimal 3 orang', 650000.00, NULL, 'none', 'active', 1, '2026-06-21 09:52:46', '2026-06-21 10:48:50'),
(215, 39, 'Dokumentasi Foto Camera + Video iPhone', 700000.00, NULL, 'drive_link', 'active', 2, '2026-06-21 09:52:46', '2026-06-21 10:48:50'),
(216, 39, 'Dokumentasi Foto + Video iPhone', 600000.00, NULL, 'drive_link', 'active', 3, '2026-06-21 09:52:46', '2026-06-21 10:48:50'),
(217, 39, 'Dokumentasi Foto Camera', 500000.00, NULL, 'drive_link', 'active', 4, '2026-06-21 09:52:46', '2026-06-21 10:48:50'),
(218, 39, 'Camera Insta360', 250000.00, NULL, 'none', 'active', 5, '2026-06-21 09:52:46', '2026-06-21 10:48:50'),
(219, 39, 'Ojek Trip Goa Macan Mati Only', 200000.00, NULL, 'none', 'active', 6, '2026-06-21 09:52:46', '2026-06-21 10:48:50'),
(220, 39, 'Ojek Trip Fullday', 400000.00, NULL, 'none', 'active', 7, '2026-06-21 09:52:46', '2026-06-21 10:48:50'),
(221, 40, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 0, '2026-06-21 09:52:46', '2026-06-21 10:42:11'),
(222, 40, 'Transportasi B - Mobil maksimal 3 orang', 650000.00, NULL, 'none', 'active', 1, '2026-06-21 09:52:46', '2026-06-21 10:42:11'),
(223, 40, 'Camera Insta360', 250000.00, NULL, 'none', 'active', 2, '2026-06-21 09:52:46', '2026-06-21 10:42:11'),
(224, 40, 'Ojek Trip Goa Sumitro Only', 200000.00, NULL, 'none', 'active', 3, '2026-06-21 09:52:46', '2026-06-21 10:42:11'),
(225, 40, 'Ojek Trip Fullday', 400000.00, NULL, 'none', 'active', 4, '2026-06-21 09:52:46', '2026-06-21 10:42:11'),
(226, 41, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 0, '2026-06-21 09:52:46', '2026-06-21 10:43:15'),
(227, 41, 'Transportasi B - Mobil maksimal 3 orang', 650000.00, NULL, 'none', 'active', 1, '2026-06-21 09:52:46', '2026-06-21 10:43:15'),
(228, 41, 'Camera Insta360', 250000.00, NULL, 'none', 'active', 2, '2026-06-21 09:52:46', '2026-06-21 10:43:15'),
(229, 41, 'Baterai Drone 20 menit', 150000.00, NULL, 'none', 'active', 3, '2026-06-21 09:52:46', '2026-06-21 10:43:15'),
(230, 41, 'Ojek Trip Goa Ngeleng Only', 200000.00, NULL, 'none', 'active', 4, '2026-06-21 09:52:46', '2026-06-21 10:43:15'),
(231, 41, 'Ojek Trip Fullday', 400000.00, NULL, 'none', 'active', 5, '2026-06-21 09:52:46', '2026-06-21 10:43:15'),
(232, 42, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 0, '2026-06-21 09:52:46', '2026-06-21 11:08:47'),
(233, 42, 'Transportasi B - Mobil maksimal 3 orang', 650000.00, NULL, 'none', 'active', 1, '2026-06-21 09:52:46', '2026-06-21 11:08:47'),
(234, 42, 'Dokumentasi Drone', 350000.00, NULL, 'drive_link', 'active', 2, '2026-06-21 09:52:46', '2026-07-01 05:29:11'),
(235, 42, 'Dokumentasi Camera', 300000.00, NULL, 'drive_link', 'active', 3, '2026-06-21 09:52:46', '2026-07-01 05:29:11'),
(236, 42, 'Ojek Trip Fullday', 400000.00, NULL, 'none', 'inactive', 4, '2026-06-21 09:52:46', '2026-07-01 05:29:11'),
(237, 43, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 0, '2026-06-21 09:52:46', '2026-06-21 11:41:31'),
(238, 43, 'Transportasi B - Mobil maksimal 3 orang', 650000.00, NULL, 'none', 'active', 1, '2026-06-21 09:52:46', '2026-06-21 11:41:31'),
(239, 43, 'Camera Insta360', 250000.00, NULL, 'none', 'active', 2, '2026-06-21 09:52:46', '2026-06-21 11:41:31'),
(240, 44, 'Transportasi A - Mobil maksimal 5 orang', 850000.00, NULL, 'none', 'active', 0, '2026-06-21 09:52:46', '2026-06-21 11:14:13'),
(241, 44, 'Transportasi B - Mobil maksimal 3 orang', 650000.00, NULL, 'none', 'active', 1, '2026-06-21 09:52:46', '2026-06-21 11:14:13'),
(242, 44, 'Camera Insta360', 250000.00, NULL, 'none', 'active', 2, '2026-06-21 09:52:46', '2026-06-21 11:14:13'),
(243, 44, 'Ojek Trip Goa Ngeleng Only', 200000.00, NULL, 'none', 'active', 3, '2026-06-21 09:52:46', '2026-06-21 11:14:13'),
(244, 44, 'Ojek Trip Fullday', 400000.00, NULL, 'none', 'active', 4, '2026-06-21 09:52:46', '2026-06-21 11:14:13'),
(245, 45, 'Camera Insta360 (unit camera, memori 32gb, monopod, 1 battery)', 250000.00, NULL, 'drive_link', 'active', 0, '2026-06-28 06:53:23', '2026-06-28 06:53:23'),
(246, 45, 'Baterai Drone (tambahan waktu terbang 20 menit) – Rp 150.000', 150000.00, NULL, 'drive_link', 'active', 1, '2026-06-28 06:53:23', '2026-06-29 02:03:31'),
(247, 45, 'Transportasi A - Sewa Mobil, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Mobil, stay 10 Jam) kapasitas maksimal 5 orang', 850000.00, 5, 'none', 'active', 2, '2026-06-28 06:53:23', '2026-06-28 06:53:23'),
(248, 45, 'Transportasi B - Sewa Mobil, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Mobil, stay 10 Jam) kapasitas maksimal 3 orang', 650000.00, 3, 'none', 'active', 3, '2026-06-28 06:53:23', '2026-06-28 06:53:23'),
(249, 45, 'Ojek Trip Goa Ngeleng Only, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Motor)', 200000.00, NULL, 'none', 'active', 4, '2026-06-28 06:53:23', '2026-06-28 06:53:23'),
(250, 45, 'Ojek Trip Fullday, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Motor, stay 10 jam)', 400000.00, NULL, 'none', 'active', 5, '2026-06-28 06:53:23', '2026-06-28 06:53:23'),
(251, 46, 'Transportasi A', 850000.00, 5, 'none', 'active', 0, '2026-07-06 10:15:53', '2026-07-06 10:15:53'),
(252, 46, 'Transportasi B', 650000.00, 3, 'none', 'active', 1, '2026-07-06 10:15:53', '2026-07-06 10:15:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `trip_documentation_links`
--

CREATE TABLE `trip_documentation_links` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `schedule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `schedule_date` date DEFAULT NULL,
  `drive_link_url` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `trip_documentation_links`
--

INSERT INTO `trip_documentation_links` (`id`, `trip_id`, `schedule_id`, `session_id`, `schedule_date`, `drive_link_url`, `created_at`, `updated_at`) VALUES
(3, 33, 4, NULL, NULL, 'https://drive.google.com/drive/folders/1rrSFJbv0N8WLyHDHD3a3ThLQOZc-gBNV', '2026-07-05 10:23:56', '2026-07-05 10:23:56'),
(4, 33, 5, NULL, NULL, 'https://drive.google.com/drive/folders/1OzfwtKJx-iOYsBYRkRZ-lCPsbsFvq3J6', '2026-07-05 10:24:19', '2026-07-05 10:24:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `trip_images`
--

CREATE TABLE `trip_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `image_url` text NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `trip_images`
--

INSERT INTO `trip_images` (`id`, `trip_id`, `image_url`, `thumbnail_url`, `sort_order`) VALUES
(576, 38, 'https://mauaproject.com/uploads/trips/0ea50e1f742a75145b1373f643b5f7bf-detail.webp', 'https://mauaproject.com/uploads/trips/b31551ac8c2f361a6af8b0fd15836804-thumb.webp', 0),
(577, 38, 'https://mauaproject.com/uploads/trips/c72d426cd4762a8b780a38988586af54-detail.webp', 'https://mauaproject.com/uploads/trips/677fbcd5af3211731684f336bb3b7ace-thumb.webp', 1),
(578, 38, 'https://mauaproject.com/uploads/trips/efca74573202ec50c4eb88cb0f0d5788-detail.webp', 'https://mauaproject.com/uploads/trips/6c4d4cd1b6fa6d4bacd99799f486df3b-thumb.webp', 2),
(579, 38, 'https://mauaproject.com/uploads/trips/be0183514d4bf21fed88b770df8a3e1d-detail.webp', 'https://mauaproject.com/uploads/trips/a75cc5cf09804a377b04ef77d55b9505-thumb.webp', 3),
(672, 41, 'https://mauaproject.com/uploads/trips/b24b653a056607a44e34548967cdc465-detail.webp', 'https://mauaproject.com/uploads/trips/d4ecc2dd5601cc63f62979916f7b9ca5-thumb.webp', 0),
(673, 41, 'https://mauaproject.com/uploads/trips/6049dae3eb765cd941ace0a79a50ae72-detail.webp', 'https://mauaproject.com/uploads/trips/3e326e5fca009af6f54e405e44a625e2-thumb.webp', 1),
(674, 41, 'https://mauaproject.com/uploads/trips/ac09a2fee8f85195396a9b975ab87b8b-detail.webp', 'https://mauaproject.com/uploads/trips/aac808d9b428f23978a2c33619635f2f-thumb.webp', 2),
(675, 41, 'https://mauaproject.com/uploads/trips/79ab08b1b69470b0e591ae14753d32d1-detail.webp', 'https://mauaproject.com/uploads/trips/dbc745891ceb82c176813d188bdf2ecf-thumb.webp', 3),
(721, 39, 'https://mauaproject.com/uploads/trips/fe7dc2516881ffb4bca05ffeab87e4f1-detail.webp', 'https://mauaproject.com/uploads/trips/a552162f3aa04726288783a93427adfc-thumb.webp', 0),
(722, 39, 'https://mauaproject.com/uploads/trips/4ae6b14981426b551c492ab023a60c5b-detail.webp', 'https://mauaproject.com/uploads/trips/67d6901e20cccc9e5c8fa1f84684f533-thumb.webp', 1),
(723, 39, 'https://mauaproject.com/uploads/trips/f426ece661cd5a0968a3ddb1681180ea-detail.webp', 'https://mauaproject.com/uploads/trips/4dbd5172de4d3777b07e628fc96332ec-thumb.webp', 2),
(724, 39, 'https://mauaproject.com/uploads/trips/ba61c6d91e964c8d9fe0c06037034951-detail.webp', 'https://mauaproject.com/uploads/trips/4442f9c489d3ab055e0cf8eea734ec00-thumb.webp', 3),
(733, 43, 'https://mauaproject.com/uploads/trips/07c66ea927860c7f8cd67651f2962957-detail.webp', 'https://mauaproject.com/uploads/trips/60359275d4d7736a42c64afa6eb12c20-thumb.webp', 0),
(734, 43, 'https://mauaproject.com/uploads/trips/8cb0f28c7accb71a87b5f2c656974e05-detail.webp', 'https://mauaproject.com/uploads/trips/198fc9d5fcd6c31e8a0f34519b5f15a5-thumb.webp', 1),
(735, 43, 'https://mauaproject.com/uploads/trips/1e0ad378f63abffed12f0cdb7ab1f480-detail.webp', 'https://mauaproject.com/uploads/trips/6af2cf19b1315396a94f81d55699c361-thumb.webp', 2),
(736, 44, 'https://mauaproject.com/uploads/trips/3126a4e0f3f3c3baf4b8c2f92f6b0841-detail.webp', 'https://mauaproject.com/uploads/trips/13898ca32c3459fbd7a7c23d0c0660f1-thumb.webp', 0),
(753, 42, 'https://mauaproject.com/uploads/trips/2fc110149e2bfd98c29ccb16fb73597e-detail.webp', 'https://mauaproject.com/uploads/trips/c9a588d57f77669540d2a18a5c986142-thumb.webp', 0),
(754, 42, 'https://mauaproject.com/uploads/trips/b77ee6c265139897cb053d62112f31ab-detail.webp', 'https://mauaproject.com/uploads/trips/356988229e02b8f0ff6c2c1022841d93-thumb.webp', 1),
(755, 42, 'https://mauaproject.com/uploads/trips/ad437e7ba8b864020d3d1a47ba4847fc-detail.webp', 'https://mauaproject.com/uploads/trips/ba4b3da49a08471d9ac147de6adc6fa8-thumb.webp', 2),
(756, 42, 'https://mauaproject.com/uploads/trips/d91bf9258d056fae065ae63a875bd7af-detail.webp', 'https://mauaproject.com/uploads/trips/b81c4b68fede18460d7a0f9186d9cd5a-thumb.webp', 3),
(769, 35, 'https://mauaproject.com/uploads/trips/0cb14c0c6c22b9e3fd59af4c0e7c6851-detail.webp', 'https://mauaproject.com/uploads/trips/6ba65ed7434dd6a763ee197c8cf5ca84-thumb.webp', 0),
(770, 35, 'https://mauaproject.com/uploads/trips/5f4aa576dd833031838e42e856e71511-detail.webp', 'https://mauaproject.com/uploads/trips/31199660669ec9fd2e5b373be8ce0ae7-thumb.webp', 1),
(771, 35, 'https://mauaproject.com/uploads/trips/7a9a90532fcdcc965eff8d260af33474-detail.webp', 'https://mauaproject.com/uploads/trips/28a04933a508244c66e22719d7e25db3-thumb.webp', 2),
(772, 35, 'https://mauaproject.com/uploads/trips/970ab2dcb5efaf1390818722dd78f51a-detail.webp', 'https://mauaproject.com/uploads/trips/0dc4af9ded56615584bbe21967931b2b-thumb.webp', 3),
(773, 37, 'https://mauaproject.com/uploads/trips/410b4ba3406be3e2e7773577be1197b3-detail.webp', 'https://mauaproject.com/uploads/trips/78c4506095de46262c1c7676c34e3480-thumb.webp', 0),
(774, 37, 'https://mauaproject.com/uploads/trips/d0d829095fc6c01ae64580809f8540d8-detail.webp', 'https://mauaproject.com/uploads/trips/e3e306dffa1804c1cc7cd4f4f98cf0de-thumb.webp', 1),
(775, 37, 'https://mauaproject.com/uploads/trips/dd989ffac0cdc1b0da45642e7eb1319d-detail.webp', 'https://mauaproject.com/uploads/trips/28c551bd60aad90456d9a67603826947-thumb.webp', 2),
(776, 37, 'https://mauaproject.com/uploads/trips/76ad791a7800b01c007792537c63fbe1-detail.webp', 'https://mauaproject.com/uploads/trips/c4c26bffe06d2c59b0d53ea59be78909-thumb.webp', 3),
(777, 40, 'https://mauaproject.com/uploads/trips/9415e8bc2fd982b925836976279aad6c-detail.webp', 'https://mauaproject.com/uploads/trips/d9121a96ff7d856e3bda955343a4cca7-thumb.webp', 0),
(778, 40, 'https://mauaproject.com/uploads/trips/ace58197da80fa4b4b9c4f9daa9798a3-detail.webp', 'https://mauaproject.com/uploads/trips/d40ac8e81735d6db0140b78c2fa617c0-thumb.webp', 1),
(779, 40, 'https://mauaproject.com/uploads/trips/a66cbaafa78b2884025d9e726d2c2fad-detail.webp', 'https://mauaproject.com/uploads/trips/e7cd5cfaa4eb3c788928226dfa9e1dc8-thumb.webp', 2),
(780, 40, 'https://mauaproject.com/uploads/trips/f4626fcaf4b569f6295ed2480ddb35e3-detail.webp', 'https://mauaproject.com/uploads/trips/de1528d5003bacc4f3778e4cf4ce71dd-thumb.webp', 3),
(781, 45, 'https://mauaproject.com/uploads/trips/8dad6e6e845b1c5b2343df5bd587cb6c-detail.webp', 'https://mauaproject.com/uploads/trips/241ce0b7dd56d734718fe97a007f5b08-thumb.webp', 0),
(782, 45, 'https://mauaproject.com/uploads/trips/a5150e4b2583fd071c0c86f934c1109c-detail.webp', 'https://mauaproject.com/uploads/trips/e18c16c9765c329d720c4ed6265b06ea-thumb.webp', 1),
(783, 45, 'https://mauaproject.com/uploads/trips/bea9eb0bf967a68f8e7b0171cf8702a1-detail.webp', 'https://mauaproject.com/uploads/trips/3ac4ce6bee17ef86cd956204274760ca-thumb.webp', 2),
(784, 45, 'https://mauaproject.com/uploads/trips/34198b7109fd133af89f173dec847a3a-detail.webp', 'https://mauaproject.com/uploads/trips/c9b155b0e4fd9bb4ba1543b466cacf62-thumb.webp', 3),
(813, 36, 'https://mauaproject.com/uploads/trips/c09cdc64d397bb3343a09ae4c5cfb5ae-detail.webp', 'https://mauaproject.com/uploads/trips/897d287566df014083122a42292a9e4c-thumb.webp', 0),
(814, 36, 'https://mauaproject.com/uploads/trips/d4eff44c5d7d2bf8f94761bf098eddbb-detail.webp', 'https://mauaproject.com/uploads/trips/96fc8be2a261a852f4228ea352c72860-thumb.webp', 1),
(815, 36, 'https://mauaproject.com/uploads/trips/2e7adc2c4a6780148a007005f1e4e173-detail.webp', 'https://mauaproject.com/uploads/trips/53e49301ed8ece14fa95b67b831a9278-thumb.webp', 2),
(816, 36, 'https://mauaproject.com/uploads/trips/5d3e6174c8602d3974362478b6a4cd20-detail.webp', 'https://mauaproject.com/uploads/trips/000580109ebf1884f0a54c0715665baf-thumb.webp', 3),
(821, 34, 'https://mauaproject.com/uploads/trips/150e5158da75257ebc0d406aa9b01760-detail.webp', 'https://mauaproject.com/uploads/trips/0d623650e13ee43ddaefd72067b34b65-thumb.webp', 0),
(822, 34, 'https://mauaproject.com/uploads/trips/8644f6b08123436d936e784c2929dae5-detail.webp', 'https://mauaproject.com/uploads/trips/116c9a721d792202c81982d9c59d468d-thumb.webp', 1),
(823, 34, 'https://mauaproject.com/uploads/trips/eaba45d75f3f4a773826c016fbb486d7-detail.webp', 'https://mauaproject.com/uploads/trips/5fc3a3beb3a36ecd1ed055b83dc448d4-thumb.webp', 2),
(824, 34, 'https://mauaproject.com/uploads/trips/0954ee2732c79b9eb7aa7a49a1475b09-detail.webp', 'https://mauaproject.com/uploads/trips/42ba2185d06b53479e45a4ea74978e29-thumb.webp', 3),
(825, 33, 'https://mauaproject.com/uploads/trips/5955bb38a34b305032e46c08c347309e-detail.webp', 'https://mauaproject.com/uploads/trips/1e6cede0d21f2948eb4baad1a38bb01c-thumb.webp', 0),
(826, 33, 'https://mauaproject.com/uploads/trips/e0778e6a6b4478ec92b7ee71c9f14d70-detail.webp', 'https://mauaproject.com/uploads/trips/9e55959627f2e66d93547a77303a90c9-thumb.webp', 1),
(827, 33, 'https://mauaproject.com/uploads/trips/5e24154ad8b5a1a62783b302169c3a04-detail.webp', 'https://mauaproject.com/uploads/trips/aa1d67bc85993425c45505d4485946ee-thumb.webp', 2),
(828, 33, 'https://mauaproject.com/uploads/trips/e92597499b8c8f0a81ec5e258490979c-detail.webp', 'https://mauaproject.com/uploads/trips/42a480bab2ef71e41b89049938f5189b-thumb.webp', 3),
(830, 46, 'https://mauaproject.com/uploads/trips/520e68520eeecb46c4046282a87b33cf-detail.webp', 'https://mauaproject.com/uploads/trips/3a4b243a0a492aace3bd1e6a210dfa14-thumb.webp', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `trip_schedules`
--

CREATE TABLE `trip_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `schedule_code` varchar(50) DEFAULT NULL,
  `session_name` varchar(100) NOT NULL DEFAULT 'Sesi 1',
  `schedule_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `drive_link_url` text DEFAULT NULL,
  `visible_until` date DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `quota` int(11) NOT NULL DEFAULT 0,
  `booked_count` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','full','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `trip_schedules`
--

INSERT INTO `trip_schedules` (`id`, `trip_id`, `schedule_code`, `session_name`, `schedule_date`, `start_time`, `end_time`, `drive_link_url`, `visible_until`, `archived_at`, `quota`, `booked_count`, `status`, `created_at`) VALUES
(4, 33, 'GOA_NGELENG-20260705', 'Sesi Pagi', '2026-07-05', '08:00:00', '12:00:00', 'https://drive.google.com/drive/folders/1rrSFJbv0N8WLyHDHD3a3ThLQOZc-gBNV', '2026-07-06', NULL, 4, 4, 'inactive', '2026-06-21 09:52:46'),
(5, 33, 'GOA_NGELENG-20260712', 'Sesi Siang', '2026-07-05', '13:30:00', '17:30:00', 'https://drive.google.com/drive/folders/1OzfwtKJx-iOYsBYRkRZ-lCPsbsFvq3J6', '2026-07-06', NULL, 6, 6, 'full', '2026-06-21 09:52:46'),
(8, 34, 'GOA_SUMITRO-20260712', 'Sesi 1', '2026-07-12', '08:00:00', '13:00:00', NULL, '2026-07-13', NULL, 5, 1, 'active', '2026-06-21 09:52:46'),
(9, 34, 'GOA_SUMITRO-20260726', 'Sesi 2', '2026-07-26', '08:00:00', '13:00:00', NULL, '2026-07-27', NULL, 8, 1, 'active', '2026-06-21 09:52:46'),
(10, 35, 'GOA_MACAN_MATI-20260726', 'Sesi 1', '2026-07-26', '08:00:00', '14:00:00', NULL, '2026-07-27', NULL, 5, 0, 'active', '2026-06-21 09:52:46'),
(11, 36, 'JOMBLANG-W1-20260701', 'Sesi 1', '2026-07-01', '08:00:00', '13:00:00', NULL, '2026-07-02', '2026-07-06 08:00:25', 20, 0, 'inactive', '2026-06-21 09:52:46'),
(12, 36, 'JOMBLANG-W1-20260702', 'Sesi 1', '2026-07-02', '08:00:00', '13:00:00', NULL, '2026-07-03', '2026-07-06 08:00:25', 20, 0, 'inactive', '2026-06-21 09:52:46'),
(13, 36, 'JOMBLANG-W1-20260703', 'Sesi 1', '2026-07-03', '08:00:00', '13:00:00', NULL, '2026-07-04', '2026-07-06 08:00:25', 20, 0, 'inactive', '2026-06-21 09:52:46'),
(14, 36, 'JOMBLANG-W1-20260704', 'Sesi 1', '2026-07-04', '08:00:00', '13:00:00', NULL, '2026-07-05', '2026-07-06 08:00:25', 20, 3, 'inactive', '2026-06-21 09:52:46'),
(15, 36, 'JOMBLANG-W1-20260705', 'Sesi 1', '2026-07-05', '08:00:00', '13:00:00', NULL, '2026-07-06', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(16, 36, 'JOMBLANG-W1-20260706', 'Sesi 1', '2026-07-06', '08:00:00', '13:00:00', NULL, '2026-07-07', NULL, 20, 2, 'active', '2026-06-21 09:52:46'),
(17, 36, 'JOMBLANG-W1-20260707', 'Sesi 1', '2026-07-07', '08:00:00', '13:00:00', NULL, '2026-07-08', NULL, 20, 2, 'active', '2026-06-21 09:52:46'),
(18, 36, 'JOMBLANG-W2-20260708', 'Sesi 1', '2026-07-08', '08:00:00', '13:00:00', NULL, '2026-07-09', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(19, 36, 'JOMBLANG-W2-20260709', 'Sesi 1', '2026-07-09', '08:00:00', '13:00:00', NULL, '2026-07-10', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(20, 36, 'JOMBLANG-W2-20260710', 'Sesi 1', '2026-07-10', '08:00:00', '13:00:00', NULL, '2026-07-11', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(21, 36, 'JOMBLANG-W2-20260711', 'Sesi 1', '2026-07-11', '08:00:00', '13:00:00', NULL, '2026-07-12', NULL, 20, 4, 'active', '2026-06-21 09:52:46'),
(22, 36, 'JOMBLANG-W2-20260712', 'Sesi 1', '2026-07-12', '08:00:00', '13:00:00', NULL, '2026-07-13', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(23, 36, 'JOMBLANG-W2-20260713', 'Sesi 1', '2026-07-13', '08:00:00', '13:00:00', NULL, '2026-07-14', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(24, 36, 'JOMBLANG-W2-20260714', 'Sesi 1', '2026-07-14', '08:00:00', '13:00:00', NULL, '2026-07-15', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(25, 36, 'JOMBLANG-W3-20260715', 'Sesi 1', '2026-07-15', '08:00:00', '13:00:00', NULL, '2026-07-16', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(26, 36, 'JOMBLANG-W3-20260716', 'Sesi 1', '2026-07-16', '08:00:00', '13:00:00', NULL, '2026-07-17', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(27, 36, 'JOMBLANG-W3-20260717', 'Sesi 1', '2026-07-17', '08:00:00', '13:00:00', NULL, '2026-07-18', NULL, 20, 2, 'active', '2026-06-21 09:52:46'),
(28, 36, 'JOMBLANG-W3-20260718', 'Sesi 1', '2026-07-18', '08:00:00', '13:00:00', NULL, '2026-07-19', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(29, 36, 'JOMBLANG-W3-20260719', 'Sesi 1', '2026-07-19', '08:00:00', '13:00:00', NULL, '2026-07-20', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(30, 36, 'JOMBLANG-W3-20260720', 'Sesi 1', '2026-07-20', '08:00:00', '13:00:00', NULL, '2026-07-21', NULL, 20, 1, 'active', '2026-06-21 09:52:46'),
(31, 36, 'JOMBLANG-W3-20260721', 'Sesi 1', '2026-07-21', '08:00:00', '13:00:00', NULL, '2026-07-22', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(32, 36, 'JOMBLANG-W4-20260722', 'Sesi 1', '2026-07-22', '08:00:00', '13:00:00', NULL, '2026-07-23', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(33, 36, 'JOMBLANG-W4-20260723', 'Sesi 1', '2026-07-23', '08:00:00', '13:00:00', NULL, '2026-07-24', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(34, 36, 'JOMBLANG-W4-20260724', 'Sesi 1', '2026-07-24', '08:00:00', '13:00:00', NULL, '2026-07-25', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(35, 36, 'JOMBLANG-W4-20260725', 'Sesi 1', '2026-07-25', '08:00:00', '13:00:00', NULL, '2026-07-26', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(36, 36, 'JOMBLANG-W4-20260726', 'Sesi 1', '2026-07-26', '08:00:00', '13:00:00', NULL, '2026-07-27', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(37, 36, 'JOMBLANG-W4-20260727', 'Sesi 1', '2026-07-27', '08:00:00', '13:00:00', NULL, '2026-07-28', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(38, 36, 'JOMBLANG-W4-20260728', 'Sesi 1', '2026-07-28', '08:00:00', '13:00:00', NULL, '2026-07-29', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(39, 36, 'JOMBLANG-W4-20260729', 'Sesi 1', '2026-07-29', '08:00:00', '13:00:00', NULL, '2026-07-30', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(40, 36, 'JOMBLANG-W4-20260730', 'Sesi 1', '2026-07-30', '08:00:00', '13:00:00', NULL, '2026-07-31', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(41, 36, 'JOMBLANG-W4-20260731', 'Sesi 1', '2026-07-31', '08:00:00', '13:00:00', NULL, '2026-08-01', NULL, 20, 0, 'active', '2026-06-21 09:52:46'),
(42, 37, 'PADDLE_BOARD-20260711', 'Sesi Pagi', '2026-07-11', '08:00:00', '12:00:00', NULL, '2026-07-12', NULL, 6, 0, 'active', '2026-06-21 09:52:46'),
(56, 33, 'schedule_3', 'Sesi Pagi', '2026-07-12', '08:00:00', '12:00:00', NULL, '2026-07-13', NULL, 4, 4, 'active', '2026-06-22 03:20:15'),
(57, 33, 'schedule_4', 'Sesi Siang', '2026-07-12', '13:30:00', '17:30:00', NULL, '2026-07-13', NULL, 5, 5, 'full', '2026-06-22 03:27:49'),
(58, 33, 'schedule_5', 'Sesi Pagi', '2026-07-19', '08:00:00', '12:00:00', NULL, '2026-07-20', NULL, 5, 5, 'full', '2026-06-22 03:27:49'),
(59, 33, 'schedule_6', 'Sesi Siang', '2026-07-19', '13:30:00', '17:30:00', NULL, '2026-07-20', NULL, 4, 4, 'active', '2026-06-22 03:27:49'),
(60, 33, 'schedule_7', 'Sesi Pagi', '2026-07-26', '08:00:00', '12:00:00', NULL, '2026-07-27', NULL, 5, 5, 'full', '2026-06-22 03:27:49'),
(61, 33, 'schedule_8', 'Sesi Siang', '2026-07-26', '13:30:00', '17:30:00', NULL, '2026-07-27', NULL, 5, 2, 'active', '2026-06-22 03:27:49'),
(66, 37, 'schedule_2', 'Sesi Siang', '2026-07-11', '13:30:00', '17:00:00', NULL, '2026-07-12', NULL, 6, 0, 'inactive', '2026-06-24 06:43:54'),
(67, 37, 'schedule_3', 'Sesi Pagi', '2026-07-18', '08:00:00', '12:00:00', NULL, '2026-07-19', NULL, 6, 0, 'active', '2026-06-24 06:43:54'),
(68, 37, 'schedule_4', 'Sesi Siang', '2026-07-18', '13:30:00', '17:00:00', NULL, '2026-07-19', NULL, 6, 0, 'inactive', '2026-06-24 06:43:54'),
(69, 37, 'schedule_5', 'Sesi Pagi', '2026-07-19', '08:00:00', '12:00:00', NULL, '2026-07-20', NULL, 6, 0, 'active', '2026-06-24 06:43:54'),
(70, 37, 'schedule_6', 'Sesi Siang', '2026-07-19', '13:30:00', '17:00:00', NULL, '2026-07-20', NULL, 6, 0, 'inactive', '2026-06-24 06:43:54'),
(71, 33, 'schedule_9', 'Sesi 9', '2026-07-13', '13:30:00', '17:30:00', NULL, '2026-07-14', NULL, 5, 0, 'active', '2026-07-05 11:06:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `trip_sessions`
--

CREATE TABLE `trip_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `session_code` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `drive_link_url` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `trip_sessions`
--

INSERT INTO `trip_sessions` (`id`, `trip_id`, `session_code`, `name`, `start_time`, `end_time`, `drive_link_url`, `status`) VALUES
(1, 38, 'REGULER', 'Reguler', '08:00:00', '13:00:00', NULL, 'active'),
(2, 39, 'PAGI', 'Pagi', '08:00:00', '12:00:00', NULL, 'active'),
(3, 40, 'PAGI', 'Pagi', '08:00:00', '12:00:00', NULL, 'active'),
(4, 40, 'SIANG', 'Siang', '08:00:00', '12:00:00', NULL, 'active'),
(5, 41, 'PAGI', 'Pagi', '08:00:00', '12:00:00', NULL, 'active'),
(6, 41, 'SIANG', 'Siang', '13:30:00', '17:30:00', NULL, 'active'),
(7, 42, 'PAGI', 'Sesi Pagi', '08:00:00', '12:00:00', NULL, 'active'),
(8, 42, 'SIANG', 'Sesi Siang', '13:30:00', '17:00:00', NULL, 'active'),
(9, 43, 'SESI_0430', 'Sesi 04.30', '04:30:00', '06:00:00', NULL, 'active'),
(10, 43, 'SESI_0600', 'Sesi 06.00', '06:00:00', '07:30:00', NULL, 'active'),
(11, 43, 'SESI_0800', 'Sesi 08.00', '08:00:00', '09:30:00', NULL, 'active'),
(12, 43, 'SESI_1100', 'Sesi 11.00', '11:00:00', '12:30:00', NULL, 'active'),
(13, 43, 'SESI_1400', 'Sesi 14.00', '14:00:00', '15:30:00', NULL, 'active'),
(14, 43, 'SESI_1600', 'Sesi 16.00', '16:00:00', '17:30:00', NULL, 'active'),
(15, 44, 'REGULER', 'Sesi 3', '12:00:00', '14:00:00', NULL, 'active'),
(16, 44, 'session_2', 'Sesi 4', '14:00:00', '16:00:00', NULL, 'active'),
(19, 44, 'session_5', 'Sesi 1', '08:00:00', '10:00:00', NULL, 'active'),
(20, 44, 'session_6', 'Sesi 2', '10:00:00', '12:00:00', NULL, 'active'),
(21, 45, 'session_1', 'Sesi 1', '08:00:00', '12:00:00', NULL, 'active'),
(22, 45, 'session_2', 'Sesi 2', '13:30:00', '17:30:00', NULL, 'active'),
(23, 46, 'session_1', 'Sesi 1', '08:00:00', '16:00:00', NULL, 'active');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(30) DEFAULT NULL,
  `role` enum('admin','customer','worker') NOT NULL DEFAULT 'customer',
  `address` text DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `health_notes` text DEFAULT NULL,
  `blood_type` varchar(20) DEFAULT NULL,
  `height_cm` smallint(5) UNSIGNED DEFAULT NULL,
  `weight_kg` decimal(5,2) UNSIGNED DEFAULT NULL,
  `shoe_size` decimal(4,1) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified`, `email_verified_at`, `password_hash`, `whatsapp`, `role`, `address`, `age`, `gender`, `health_notes`, `blood_type`, `height_cm`, `weight_kg`, `shoe_size`, `created_at`, `updated_at`) VALUES
(55, 'Admin Maua', 'thisismaua@gmail.com', 1, '2026-06-21 09:56:29', '$2y$10$alHIzuEU8abvLoHm0ERhXOIairfV/8dinJ3RqMU6eQhZ5NNf1338i', NULL, 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-21 09:56:29', '2026-06-21 09:56:29'),
(56, 'Putra 05', 'anugrahags05@gmail.com', 1, '2026-06-21 09:58:25', '$2y$10$enaUKwVQevhTYER8wuplZ.rDX19SPLYVtqrT7hpHlq0rJScIdscf6', '085702055011', 'customer', 'Yogyakarta', 22, 'Laki-laki', '-', 'A', 178, 73.00, 43.0, '2026-06-21 09:58:25', '2026-06-23 13:27:05'),
(57, 'Zakki Azizah', 'zakkiazizahh@gmail.com', 1, '2026-06-21 15:00:06', '$2y$10$4DDj63WzXFh4r/xJ2MJ6euA3lxjAPvmZ9x.Ilpw9TT2fZfKOCljqu', '082133393360', 'customer', 'Yogyakarta', 25, 'Perempuan', '-', 'O', 153, 43.00, 38.0, '2026-06-21 15:00:06', '2026-06-25 16:15:01'),
(59, 'Saputra 04', 'anugrahags04@gmail.com', 1, '2026-06-23 19:23:20', '$2y$10$ZIoUp..1Y2jEphJZShBPx.mbl9yadevFpVTHGruHnziOWB/pXnMNy', '085702055011', 'customer', 'Yogyakarta', 22, 'Laki-laki', 'Sehat Walafiat', 'A', 178, 73.00, 43.0, '2026-06-23 12:23:20', '2026-06-24 15:34:04'),
(61, 'Muhammad An Nizar', 'nizar.aan15@gmail.com', 1, '2026-06-24 12:17:44', '$2y$10$ttieH2Dm7qSuKTPmRw.IW.jh0mjtolx1c5gSztp.b1alMs6MOGd4.', NULL, 'worker', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-24 05:17:44', '2026-06-24 05:17:44'),
(62, 'Hanifah', 'hanyamanusiabiasa04@gmail.com', 1, '2026-06-25 10:45:35', '$2y$10$h8x9TbNjjmZIRyZiFEQqF.o/IYgkzAD.zfFLN9SaDswSTfLnvn1qi', '081393611933', 'customer', 'Koripan 1, Dlingo', 28, 'Perempuan', '-', 'B', 160, 60.00, 37.0, '2026-06-25 03:45:35', '2026-06-25 03:53:25'),
(63, 'Evelyn Tan', 'evelyntannnn@gmail.com', 1, '2026-06-25 10:47:37', '$2y$10$0qd3cMqJYwRcCVByIRGPa.T0Yn2vJsLlmFUJIkoJJbgFjck49UJE2', '081230744536', 'customer', 'Jl Argopuro No 43', 27, 'Perempuan', '-', 'O', 160, 48.00, 38.0, '2026-06-25 03:47:37', '2026-06-25 04:19:55'),
(64, 'Muhammad Fauzi', 'mfauzi.muslimin@gmail.com', 1, '2026-06-25 10:50:13', '$2y$10$Ahy19.MZnS5pqAubAMrGFuz9B7dIZpqCx7kf3XNs1PRfV2i.Thimu', NULL, 'worker', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 03:50:13', '2026-06-25 03:50:13'),
(65, 'Azizah Suwitaningtyas Azzahra', 'azizaharhazza@gmail.com', 1, '2026-06-25 10:53:26', '$2y$10$09p9k934ZdR486Fz0bqG/OohWZxW9/c6BoNWOydilob1JxLo8/3DW', '085748489200', 'customer', 'Madiun', 26, 'Perempuan', 'tidak ada', 'O', 159, 48.00, 40.0, '2026-06-25 03:53:26', '2026-06-25 03:59:55'),
(66, 'Availa Okta Zafarani', 'availaokta123@gmail.com', 1, '2026-06-25 11:01:53', '$2y$10$rDx5zfL2vrjRVYkjNqS.UO/Hd7xvUeFWLuIfNspigYVKzg9U.OUTm', '085824857856', 'customer', 'Kontrakan Wakil Bonyan, Cikedokan, Cikarang Barat, Bekasi', 22, 'Perempuan', '-', 'O', 158, 45.00, 38.0, '2026-06-25 04:01:53', '2026-06-25 04:01:53'),
(67, 'Anisa Ulyurinda', 'anisaulyu00@gmail.com', 1, '2026-06-25 11:21:41', '$2y$10$EHEny0SDHT4esRVlzNyoXuonjyDReTKpSX/VsFqtQra5.zgrdgFoi', '087754710338', 'customer', 'Bandar lampung', 20, 'Perempuan', '-', 'A', 157, 53.00, 39.0, '2026-06-25 04:21:41', '2026-06-25 04:21:41'),
(68, 'Mufti Hidayat Amin', 'muftidakota90@gmail.com', 1, '2026-06-25 12:00:01', '$2y$10$46/ZzloXCKv.acHA3PED/ulYNhOtXD/rOsNsg2x9y8Y0eGJFZVvsu', '081228323703', 'customer', 'Semarang', 35, 'Laki-laki', 'asam lambung', 'Tidak tahu', 166, 52.00, 41.0, '2026-06-25 05:00:01', '2026-06-25 05:17:16'),
(69, 'Siti Muzayanah', 'anamuzayanah000@gmail.com', 1, '2026-06-25 12:09:20', '$2y$10$jI2MHky9mNVrAnk3OF5amOuUTxZA4KWMTXUxZAoZSNt.2j9TZBvYe', '081227774394', 'customer', 'Rembang', 26, 'Perempuan', '‘_’', 'A', 157, 65.00, 41.0, '2026-06-25 05:09:20', '2026-06-25 05:14:55'),
(70, 'Fanani Rahmiyah Ariwigati', 'kirananona1516@gmail.com', 1, '2026-06-25 12:35:06', '$2y$10$hVBb0J.DjYYkLUQ73keCuuyplUqqnIjAfTig5.5/PrVodJmoLeZuO', '082140701846', 'customer', 'Surabaya', 35, 'Perempuan', '-', 'B', 164, 45.00, 37.0, '2026-06-25 05:35:06', '2026-06-29 09:18:12'),
(83, 'Ummi Kalsum', 'ummikalsumn@gmail.com', 1, '2026-06-25 12:48:19', '$2y$10$wjB6b5B9cer1jzQgn4JXO.b.DFHeEHHMnxqjAoq1AW6wmtCcUaJVa', '085163525498', 'customer', 'jl paccerakkang 135', 28, 'Perempuan', 'asam lambung', 'A', 163, 72.00, 40.0, '2026-06-25 05:48:19', '2026-06-25 05:54:18'),
(108, 'Zakkiatuz Zahrolazizah', 'mauacollect@gmail.com', 1, '2026-06-25 12:51:03', '$2y$10$W800UFS0vPyIZgtt/Nv1uu9ao/bku4p7qFTWEl16jJQHvm9boPUUG', NULL, 'worker', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 05:51:03', '2026-06-25 05:51:03'),
(109, 'Rifka Wangiana Yulia Putri', 'aerisexol6@gmail.com', 1, '2026-06-25 14:17:19', '$2y$10$8wpGTG9g/kncV3JqpSMvHeqM80m2zMwOsxVOzudQAxjI5hl1ZivZm', '083861293708', 'customer', 'Jl Merdeka 92 Pasuruhan Binangun', 29, 'Perempuan', '-', 'O', 155, 44.00, 37.0, '2026-06-25 07:17:19', '2026-06-25 07:19:35'),
(110, 'Yusia Harnanda', 'yusiaharnanda0@gmail.com', 1, '2026-06-25 14:30:36', '$2y$10$yaw793q36r7r6836SrIpouvh65h6Pgf3yR77cfdEPj6Q1tv0BiD/y', '085880063476', 'customer', 'Banjarbaru ', 29, 'Perempuan', '-', 'B', 158, 47.00, 37.0, '2026-06-25 07:30:36', '2026-06-25 07:36:31'),
(111, 'Al Fitra Salim As Syifa', 'alfitraasyifa@gmail.com', 1, '2026-06-25 14:50:16', '$2y$10$8IzItsxqZ.FRELyZ2S4CZ.zPOwCWvZQI4bd75vkaR6d6gn/Ufi4te', '089512461392', 'customer', 'Jl. Dinar Mas XVI/18 Semarang', 26, 'Perempuan', '-', 'O', 156, 55.00, 37.0, '2026-06-25 07:50:16', '2026-06-25 08:16:22'),
(112, 'Arvin Sujitno', 'sujitnoarvin@gmail.com', 1, '2026-06-25 15:26:32', '$2y$10$1QyCXwVtxfYlwfXHtfOGwebQnIFH40ZvzHNc4qIq1DeGputzzFY.q', '085155288835', 'customer', 'Jalan Thamrin Gang Lawang 1 No. 40-A', 27, 'Laki-laki', '-', 'B', 185, 77.00, 45.0, '2026-06-25 08:26:32', '2026-06-27 07:21:59'),
(113, 'Frisca Zahra Lestari', 'zahrafriscalestari@gmail.com', 1, '2026-06-25 20:25:28', '$2y$10$AuVbNty1m8HvB2HAHePIgOS94FZr9SXxYJA1/5dcfjdjkDHqGASa2', '0818221104', 'customer', 'Tangerang Selatan', 19, 'Perempuan', '-', 'A', 160, 62.00, 39.0, '2026-06-25 13:25:28', '2026-06-25 13:25:28'),
(115, 'Thalia Charisma', 'thaliacharisma@gmail.com', 1, '2026-06-26 07:47:46', '$2y$10$2/RZxIGEM67/9P7/zPzibu1kYcxdFHRgANqZkvTrP9Tg0qjHf5Hj6', '081231539850', 'customer', 'Jakarta', 28, 'Perempuan', '-', 'Tidak tahu', 160, 54.00, 38.0, '2026-06-26 00:47:46', '2026-06-26 00:57:26'),
(116, 'Tasya Aulia', 'tasyaauliia27@gmail.com', 1, '2026-06-26 11:49:30', '$2y$10$noL.vDdnIJ/XPMxyZyl79OTiJinsanVX5xvi8wTVX/R5DVCJFeoBm', '082286955532', 'customer', 'Palembang', 20, 'Perempuan', '', 'O', 163, 55.00, 38.0, '2026-06-26 04:49:30', '2026-06-26 04:49:30'),
(117, 'ANANDA FEBRINAWATI', 'waterfalljello.ndn@gmail.com', 1, '2026-06-26 17:02:55', '$2y$10$JCTqruAZQVbx/lVPL.VvieTGglP9xjRGuP2byMcF1r4yHzwS15B6u', '085122683229', 'customer', 'GG. BROTOSENO 1, DUSUN III, PUCANGAN, KARTASURA, SUKOHARJO, JAWA TENGAH', 20, 'Perempuan', '-', 'B', 151, 46.00, 39.0, '2026-06-26 10:02:55', '2026-07-01 09:51:02'),
(118, 'Lutfie adelia', 'lutfieadel@gmail.com', 1, '2026-06-26 19:57:33', '$2y$10$TZbDrVXZv44XqNvSXPUmIuVhBWymjeWbKeY8pggi.6VCNV6yyGLL6', '081110512226', 'customer', 'Tangerang', 27, 'Perempuan', '-', 'O', 158, 50.00, 40.0, '2026-06-26 12:57:33', '2026-06-26 12:57:33'),
(119, 'Nesi', 'nesiaureole@gmail.com', 1, '2026-06-26 21:31:33', '$2y$10$axOgjZcPuSKCKdStcaUAhe5AsmmJsAaMIO7nuQaO6/jfgx/bN5INm', '085289898355', 'customer', 'Jakarta', 35, 'Perempuan', '-', 'A', 154, 66.00, 36.0, '2026-06-26 14:31:33', '2026-06-26 14:31:33'),
(121, 'Devina Mellysa Octaviasari', 'devinamellysaocta11@gmail.com', 1, '2026-06-27 09:58:27', '$2y$10$Ni7Fk8coXrlXzu3MXwvkCunxWdL6DgCkr.vtEU4jjC8zmqP00gxjS', '085792935097', 'customer', 'Malang', 26, 'Perempuan', 'Tidak ada', 'O', 165, 54.00, 40.0, '2026-06-27 02:58:27', '2026-06-27 11:44:44'),
(122, 'RAHILDA NURUL SAKINAH', 'rahildanrls@gmail.com', 1, '2026-06-27 18:18:37', '$2y$10$pxxA59pqdJ.SJI2q1sTbleRHPaigSGmEXkPUCISooIuEajTTGHBeq', '081317404452', 'customer', 'Jakarta selatan', 29, 'Perempuan', '-', 'B', 168, 79.00, 40.0, '2026-06-27 11:18:37', '2026-06-27 11:29:04'),
(123, 'Putu Alini Pratiwi', 'aliniipratiwii2709@gmail.com', 1, '2026-06-27 19:14:06', '$2y$10$yJZ3L63xk9inAyQQDrr.aeKgbW0t2Z7cu7P/ivN97bV7keH/saGXK', '081337683674', 'customer', 'Bali, Denpasar', 17, 'Perempuan', '-', 'A', 160, 48.00, 39.0, '2026-06-27 12:14:06', '2026-06-27 12:54:55'),
(124, 'Nafisa Hidayah', 'nafisahdyh@gmail.com', 1, '2026-06-27 19:14:31', '$2y$10$GBDnj59GkJWj.XnySZucAukzJ0XQjP5OIJQGaAIAIH9xM04lDhQ2C', '081568392871', 'customer', 'Sukoharjo Jawa Tengah', 19, 'Perempuan', '-', 'O', 155, 42.00, 39.0, '2026-06-27 12:14:31', '2026-06-27 12:18:46'),
(125, 'Saputra', 'saputra@gmail.com', 1, '2026-06-28 06:35:38', '$2y$10$H1gduVOlU4qS2sPqD1EqgeY12iXFeYKCxEltA9SCXouwkDFmhnn3m', NULL, 'worker', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-27 23:35:38', '2026-06-27 23:35:38'),
(126, 'Nia Kurniatiningsih', 'niakurniatiningsih59@gmail.com', 1, '2026-06-28 15:35:35', '$2y$10$iizgQbCpHbfZAy.7yX6JauX9HLp1rHA5ZbNpApqWJTM46Cdhfq/s.', '089513150201', 'customer', 'Perum Regent Park blok E1/21, Karawang', 24, 'Perempuan', '-', NULL, 161, 43.00, 39.0, '2026-06-28 08:35:35', '2026-06-28 08:35:35'),
(127, 'queen', 'queennugraha@gmail.com', 1, '2026-06-28 21:40:24', '$2y$10$8KTfD2a3GOsNpBJg4Jgy1eyQRjm5rRjeGXy3OKm2Z581TCgPJvbES', '081230627237', 'customer', 'yogya', 23, 'Perempuan', '- ', 'B', 158, 53.00, 37.0, '2026-06-28 14:40:24', '2026-06-28 14:40:24'),
(128, 'Dimas Ananta Kusuma', 'dimasananta393@gmail.com', 1, '2026-06-29 08:58:42', '$2y$10$SMfsUz./8D8eVTPT8yWMtOzeLd6Ja.6xO/qG.plQ9wsWRgyx29EmW', NULL, 'worker', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-29 01:58:42', '2026-06-29 01:58:42'),
(129, 'Fariz Ardiansyah', 'farisardiansyah151@gmail.com', 1, '2026-06-29 09:14:10', '$2y$10$K.2zhPpEXIX2y626D5Mcn.W9BmmWTtOSc1sHTVrYHzmsgE73FvgOO', NULL, 'worker', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-29 02:14:10', '2026-06-29 02:14:10'),
(130, 'Hening Asti Rahayu', 'heningasti@gmail.com', 1, '2026-06-29 14:20:37', '$2y$10$QuIS62DuCz1BSg/24I13hu8Ixrs/RpZwhw5TX7kVivQolnyH94PI6', '089644693329', 'customer', 'Kedungpring, Kemranjen, Banyumas', 29, 'Perempuan', '-', 'O', 160, 57.00, 39.0, '2026-06-29 07:20:37', '2026-06-29 07:20:37'),
(131, 'Aina Sarah Hafawati', 'sarahaina2023@gmail.com', 1, '2026-06-29 16:52:47', '$2y$10$UvccpywXlSs7UCGkB5SzVOZ8jkviK5Hx347nTZvheQ9IeNYZdSHau', '082134804769', 'customer', 'Magelang', 21, 'Perempuan', '-', 'Tidak tahu', 163, 65.00, 41.0, '2026-06-29 09:52:47', '2026-06-29 12:09:29'),
(132, 'Risti Palulun', 'ristipalulun1@gmail.com', 1, '2026-06-29 17:26:23', '$2y$10$xbc.bfuLalHle1x0gJCo.uX6fuR/.unp1BDsVjYyl6hQOhFkd8//m', '089698763898', 'customer', 'Jakarta', 25, 'Perempuan', '-', 'AB', 147, 38.00, 37.0, '2026-06-29 10:26:23', '2026-06-29 10:26:23'),
(133, 'Meimeimecin', 'tikonerlia@gmail.com', 1, '2026-06-30 08:31:54', '$2y$10$vRQOx8nmPEI1ejUy7aLTdeLRmBjNK2u3f.mcGueTFnmE4g2dewo7q', '082155530596', 'customer', 'Jakarta', 30, 'Perempuan', '-', 'O', 163, 47.00, 38.0, '2026-06-30 01:31:54', '2026-06-30 01:56:07'),
(134, 'Ainun silvi', 'ainun_silvi@icloud.com', 1, '2026-06-30 11:58:28', '$2y$10$z3rB4a8YaaQp3xPDDiAASOtFpduPQ7NM68fiSlyDdhFqQ8jrmg/1y', '085950293907', 'customer', 'Cirebon', 26, 'Perempuan', '-', 'A', 157, 53.00, 39.0, '2026-06-30 04:58:28', '2026-06-30 05:39:26'),
(135, 'Zara', 'zaahroo16@gmail.com', 1, '2026-06-30 15:14:06', '$2y$10$0VIrzuiABnev3zHeFskZxus0w8EVKJT7ycUw7h0vkfNCAUt9vla5m', '081214022975', 'customer', 'Jakarta Selatan', 28, 'Perempuan', '-', 'O', 151, 68.00, 40.0, '2026-06-30 08:14:06', '2026-06-30 08:35:34'),
(136, 'Retno Wijayanti', 'retnowjynt2@gmail.com', 1, '2026-06-30 18:14:45', '$2y$10$d1u7jG8auljNvZNDM4HIWuW1pmlXzhwzI/cS02cyDZyQMfU8ox7Fa', '089513112424', 'customer', 'Grobogan ', 20, 'Perempuan', '', NULL, 164, 63.00, 39.0, '2026-06-30 11:14:45', '2026-06-30 11:14:45'),
(137, 'Retno Wijayanti', 'rtnwy5705@gmail.com', 1, '2026-06-30 18:28:32', '$2y$10$zO9BAHurd3xCPkOCSj8oVewc8tfSVsnBcJabR3YJ7SBUUmJ50.9aa', '089513112424', 'customer', 'Grobogan ', 20, 'Perempuan', '-', 'Tidak tahu', 164, 63.00, 39.0, '2026-06-30 11:28:32', '2026-06-30 11:40:38'),
(138, 'Garin Christi Saputri', 'saputrigarin@gmail.com', 1, '2026-06-30 20:04:53', '$2y$10$uH.BmAl2vgCkJmwhbXyRC.cNQ4ysYkLD8RiTeN0dytj0m9/W7ZjzO', '08119934848', 'customer', 'Jakarta/Solo', 33, 'Perempuan', '-', 'B', 156, 48.00, 37.0, '2026-06-30 13:04:53', '2026-06-30 13:12:32'),
(139, 'Nita Silfianah', 'nitasilfianah@gmail.com', 1, '2026-07-01 13:01:13', '$2y$10$LSyC/nO9QbwL9OkA5kYRM.kZF2/7aaULwvt4ufLvnoHntSqO4KQny', '085771678734', 'customer', 'Bekasi', 28, 'Perempuan', '-', 'Tidak tahu', 161, 60.00, 40.0, '2026-07-01 06:01:13', '2026-07-01 07:50:15'),
(140, 'resty pranandari', 'restiananda10@gmail.com', 1, '2026-07-01 14:01:13', '$2y$10$gZKEQgfzbDOMGYUcCppcMO/ZVbUQLE6.ZUuKeHnV8MUDOhiKzXCRe', '082216719070', 'customer', 'tangerang kita', 32, 'Perempuan', '-', 'A', 161, 52.00, 39.0, '2026-07-01 07:01:13', '2026-07-03 03:55:23'),
(141, 'Kiveileen Nofa Malindo', 'nkiveileen2@gmail.com', 1, '2026-07-02 08:54:37', '$2y$10$h7RcrpNbjXwvLDO46tSjsexk1OaIr3puIvXEJ99qiMARzwMGXI/te', '081293944712', 'customer', 'Pekanbaru', 26, 'Perempuan', '-', 'A', 153, 49.00, 37.0, '2026-07-02 01:54:37', '2026-07-02 03:05:54'),
(142, 'Marseila Puspita Dewi', 'marseilapuspita2707@gmail.com', 1, '2026-07-02 10:12:50', '$2y$10$nHFdiVI4UZF/MnnqBydkU.p16k8zG2rWuEVxoHtPdy10lHxV.HbRS', '088219779232', 'customer', 'Bekasi', 22, 'Perempuan', 'darah rendah', 'Tidak tahu', 154, 49.00, 39.0, '2026-07-02 03:12:50', '2026-07-02 03:21:11'),
(143, 'Aurora Shelbiana Putri Darmawan', 'aurorashelby16@gmail.com', 1, '2026-07-02 10:52:31', '$2y$10$GjUOP0JDMzdjpFR4rx7A0.5Us29YlGgdYCKW468o/p6gCp07HCMPm', '087825268055', 'customer', 'Jakarta', 7, 'Perempuan', '-', 'A', 152, 51.00, 36.0, '2026-07-02 03:52:31', '2026-07-02 03:52:31'),
(144, 'Ine Lestari', 'inelestari51@gmail.com', 1, '2026-07-02 13:04:23', '$2y$10$WQ5.EhTq/amWU4MeeSsKOeq2Eq.epyy4vuDREJlXpQmRrhlSMWGrq', '082320857353', 'customer', 'Kebumen', 29, 'Perempuan', 'Tidak ada', 'O', 160, 55.00, 40.0, '2026-07-02 06:04:23', '2026-07-02 06:30:52'),
(145, 'Rengganingtyas', 'rengganingt@gmail.com', 1, '2026-07-02 17:43:46', '$2y$10$Tv5y.0uNNPWW4c8rf.LuFewS4.3gRzRjPlg54DizCnVZMQZEIvvOm', '089612134312', 'customer', 'Yogyakarta', 25, 'Perempuan', '-', 'A', 158, 43.00, 39.0, '2026-07-02 10:43:46', '2026-07-02 12:06:19'),
(146, 'Amelia Maharani Nurmalitasari', 'aameliarani@gmail.com', 1, '2026-07-02 18:16:02', '$2y$10$SdM2VoIP3voFOp0pthYmpOTMOmTZIm5nxzOA0XP6t2aJ9THfHzGsC', '082308230800', 'customer', 'Jakarta', 25, 'Perempuan', '', 'O', 155, 54.00, 39.0, '2026-07-02 11:16:02', '2026-07-02 11:16:02'),
(147, 'Fhadia Andini', 'fhadia23@gmail.com', 1, '2026-07-02 19:47:13', '$2y$10$UZWe1dXqLziERVixfskCLelvZ4D4CGWA/mUH9Fl4f/RQJKTn6OB.2', '081288319976', 'customer', 'Solo', 22, 'Perempuan', 'Maagh', NULL, 165, 56.00, 39.0, '2026-07-02 12:47:13', '2026-07-02 12:56:24'),
(148, 'Galih Satria', 'galihstriaa@gmail.com', 1, '2026-07-02 19:57:21', '$2y$10$pvjU/rXI5eEI2bgJ/eVMwO8paqS8wuoyhv71jBz.VGPyj.aEx2vGi', '081398723012', 'customer', 'Bekasi', 20, 'Laki-laki', '-', 'B', 170, 50.00, 41.0, '2026-07-02 12:57:21', '2026-07-02 12:57:21'),
(149, 'Firdha Widya Sari', 'firdawidya123go@gmail.com', 1, '2026-07-03 02:01:36', '$2y$10$0OGv3PArpN8wxPkPO5Ec3.1AWT/h1.hdu1DEgsl3l6XbXD6ck4JfK', '085607879797', 'customer', 'Pasuruan', 24, 'Perempuan', '-', 'A', 153, 67.00, 40.0, '2026-07-02 19:01:36', '2026-07-05 10:25:02'),
(150, 'Santi Rachmawati', 'ibnukevran@gmail.com', 1, '2026-07-03 17:01:54', '$2y$10$58NHl9MmefUTA6T1llPCwuKXs.1xxwnhgu9If2Dd95iVgsM4etV0i', '081328960950', 'customer', 'Jogjakarta', 43, 'Perempuan', '-', 'A', 160, 53.00, 39.0, '2026-07-03 10:01:54', '2026-07-05 15:31:54'),
(151, 'Mar\'atus Sholehah', 'marsolika@gmail.com', 1, '2026-07-04 13:48:24', '$2y$10$j5sVT6izf5BD/e87qGGbfOJ6D6jBMNny5zp/hagfVg0aX2f747H..', '081390021819', 'customer', 'Karanganyar', 29, 'Perempuan', 'Tidak ada', 'O', 155, 60.00, 38.0, '2026-07-04 06:48:24', '2026-07-04 15:12:31'),
(152, 'Olivia Audrey', 'oliviaaudreyy@gmail.com', 1, '2026-07-04 21:16:37', '$2y$10$N0of1EUCDjwiR6yNBGlvgu0wd/x6solPIPc/DuLBPQ/7a/qJGB4Xa', '087888080896', 'customer', 'Jakarta', 29, 'Perempuan', '-', 'A', 150, 52.00, 36.0, '2026-07-04 14:16:37', '2026-07-04 14:20:17'),
(153, 'fara yunia damayanti', 'farayuniadamayanti2@gmail.com', 1, '2026-07-05 11:10:05', '$2y$10$4ykuPOz0.UAQKUTYb8.lXeVmO9C/fCu6yo9KhtlgMuQY8mkvU58ZK', '082328579898', 'customer', 'Kota Magelang', 19, 'Perempuan', '-', 'Tidak tahu', 157, 45.00, 39.0, '2026-07-05 04:10:05', '2026-07-05 04:10:05'),
(154, 'Elisya Hileri', 'ehileritei@gmail.com', 1, '2026-07-05 11:44:56', '$2y$10$AijbXt8RL9V77JWoGueo2u6RXSU7Kb..wudX8JHQQVy/XDPYEpgV2', '087888997655', 'customer', 'Jl. Kemang Raya Selatan, gg. Bersama No. 8', 30, 'Perempuan', '‘-‘', 'Tidak tahu', 160, 48.00, 37.0, '2026-07-05 04:44:56', '2026-07-05 05:10:17'),
(155, 'Gita Euaggelion Tarigan', 'gitaeuaggelion@gmail.com', 1, '2026-07-05 23:20:26', '$2y$10$xqE7OqmmZO1Fx9EitFD22O.GqRQjkpfn8yF7epmyYnnL9J5GWIMMG', '081219370224', 'customer', 'Bekasi', 22, 'Perempuan', '-', 'AB', 161, 64.00, 38.0, '2026-07-05 16:20:26', '2026-07-05 16:20:26'),
(156, 'LYDIA FISCA', 'fiscalydia@gmail.com', 1, '2026-07-06 10:54:24', '$2y$10$m1go5wLQEzKn41Q7ROUOJ.9a/BFF1dMskEEsha73vhRGtlButSlKW', '081276276676', 'customer', 'Klaten', 29, 'Perempuan', 'tidak ada', 'AB', 161, 65.00, 39.0, '2026-07-06 03:54:24', '2026-07-06 03:54:24'),
(157, 'Fanny', 'drfannyprita@gmail.com', 1, '2026-07-06 17:26:47', '$2y$10$JW5fyIfphianhTOfslg2BuIBQkPmpaRPdCszCmsUtBGWmQprWt0Xy', '081226331506', 'customer', 'Bina Griya B3-150', 35, 'Perempuan', '-', 'O', 160, 54.00, 39.0, '2026-07-06 10:26:47', '2026-07-06 10:26:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `token_hash`, `expires_at`, `created_at`, `last_used_at`) VALUES
(1, 59, '03579f9a01a96b782be3c46c3b06a47e754c98eafbc04d0ea806a4a5f025a265', '2026-07-24 18:20:49', '2026-06-24 11:20:49', '2026-06-24 18:23:19'),
(2, 55, '23b10ab6946f49bfd40f719e0cde318fadaa55b4ac2601a79b6b3cb81b33b70a', '2026-07-24 18:23:24', '2026-06-24 11:23:24', '2026-06-24 22:04:55'),
(3, 55, 'e4845cd5f41d8c0371924a9ae668f6986d0e8a2112f4858728be3f4d8f4a1362', '2026-07-24 22:05:07', '2026-06-24 15:05:07', '2026-06-24 22:05:14'),
(4, 55, 'c2e55dc128a678468bb516b0105d7335bc305cbbf430f72f3bbef2f28b3f82ef', '2026-07-24 22:05:17', '2026-06-24 15:05:17', '2026-06-24 22:06:21'),
(5, 55, '6e09cae5d02ff01866759c2690fd32ebcc1e400daecb4a3a3d930c7f204a355a', '2026-07-24 22:06:25', '2026-06-24 15:06:25', '2026-06-24 22:06:25'),
(6, 59, '8e095cf9e1b059045a84df49159a3943504f2dbf33a8cab677562f3d85a17ff8', '2026-07-24 22:10:08', '2026-06-24 15:10:08', '2026-06-24 22:10:08'),
(7, 59, 'c1d75ca913db1eef48bd696c6d3fe73e3cbf374fdf11f0a73d87e2e992553447', '2026-07-24 22:10:14', '2026-06-24 15:10:14', '2026-06-24 22:10:14'),
(8, 59, '06feaa4a6a1e5758d7610a93c3f28379e9a28b5365af08d2b1343c54f37945d2', '2026-07-24 22:10:14', '2026-06-24 15:10:14', '2026-06-24 22:10:14'),
(9, 59, 'f705df7bac600016f0d077c1b2ce2a3ab7603f683b3d58901d1cbc87707114ae', '2026-07-24 22:10:37', '2026-06-24 15:10:37', '2026-06-24 22:10:37'),
(10, 56, '7febb128a15b35d5b9989b87379926033afa11de5e940e7d57ae36b1b83b60d8', '2026-07-24 22:10:39', '2026-06-24 15:10:39', '2026-06-24 22:11:10'),
(11, 56, '2d498ec9a19c49d708c4dcc94de2453256ea8924906268e6ee7758bb567b7c10', '2026-07-24 22:11:15', '2026-06-24 15:11:15', '2026-06-24 22:11:15'),
(12, 56, 'd950e85bfc745c4bb7ca969a2d1f19d29a6cc893cd299204e671c41ac6eb0b0c', '2026-07-24 22:15:27', '2026-06-24 15:15:27', '2026-06-24 22:16:39'),
(13, 55, '8e4f53d2bb038a71f9d2a646f62ce4da2e6deee7fb37738e9ae593001a35ac18', '2026-07-24 22:16:50', '2026-06-24 15:16:50', '2026-06-24 22:16:50'),
(14, 59, '19a968defd2910a24c5fec665ccfd63c9a03cb7a493e1fb9a8b1a206d9b433ad', '2026-07-24 22:17:25', '2026-06-24 15:17:25', '2026-06-24 22:27:14'),
(15, 55, '08f0a61bbc21fff745adcc8a509ebf5355c23c288f5f0d54284f95a29193c179', '2026-07-24 22:28:06', '2026-06-24 15:28:06', '2026-06-24 22:34:43'),
(16, 59, '439f4c6325b127d036414301c56d7ec2f098c4aefe53a754f214c72bc7631e25', '2026-07-24 22:34:49', '2026-06-24 15:34:49', '2026-06-25 11:00:38'),
(17, 55, '8951beab9a67da81c74ec5fb06ff5d9d832a31b746bbc3883069510efde3ae73', '2026-07-25 09:48:12', '2026-06-25 02:48:12', '2026-06-25 10:10:22'),
(18, 59, '6849e3b2d79dc4ad72796ddd1f2076c13ede73eb1319da123a9b72248a5f4eb0', '2026-07-25 09:55:38', '2026-06-25 02:55:38', '2026-06-25 15:34:51'),
(19, 57, 'e5ef28aba2bf4cac6ff6b1b15aa9f5f78ea98249ac17972d563c26a349755d8f', '2026-07-25 10:12:09', '2026-06-25 03:12:09', '2026-06-25 10:12:09'),
(20, 57, 'c875eca1bd449c33dfbb0a07624375c7f419d0063ba1162a829148d02712b400', '2026-07-25 10:12:09', '2026-06-25 03:12:09', '2026-06-25 10:49:19'),
(21, 62, '9a9edfc096d5a91a17731c42596fc8bee1f6c88875e8050f97f6549a7b0deac8', '2026-07-25 10:45:47', '2026-06-25 03:45:47', '2026-07-05 17:10:40'),
(22, 63, '99896623fd98f1bc26807f455a3942f3c19c9d64832d0c9a0cea2b2509cb3d8a', '2026-07-25 10:47:52', '2026-06-25 03:47:52', '2026-06-25 10:47:52'),
(23, 55, 'b8cde0b4b9a206dd4f56edb239013e515876f4ea8ca760be9553c55932c8391c', '2026-07-25 10:49:28', '2026-06-25 03:49:28', '2026-06-25 10:51:32'),
(24, 57, '2dd3213baa2b4f72b32b0ebc36ac58126ae57b25fa1ab34fb2b8fd4f87650383', '2026-07-25 10:52:31', '2026-06-25 03:52:31', '2026-06-25 10:59:26'),
(25, 65, '88d8e93b0c0d9bd79f4747def07a9fabe60b7fe3ced0ff6b86a9886aecdb4249', '2026-07-25 10:53:40', '2026-06-25 03:53:40', '2026-06-25 19:33:50'),
(26, 63, 'becce20f14ed8647f509a2e03cd22a74f32c53d058a235084e3a1abc32b69010', '2026-07-25 10:55:22', '2026-06-25 03:55:22', '2026-06-27 08:12:23'),
(27, 57, 'e3dc52d29a53d27b46246fce6aef7a1babeb2c97aa5ee2172a600b9a4e1ddc6c', '2026-07-25 10:55:30', '2026-06-25 03:55:30', '2026-06-27 18:13:28'),
(28, 63, '17d30a7be0218d8528bf8a2f837d19a9ffceb13f7dfea20f77b4dab643c6ad2e', '2026-07-25 10:57:37', '2026-06-25 03:57:37', '2026-06-26 12:34:14'),
(29, 55, 'f761be5d34804fa64de09e68bd8a90bd7926d2b607e53283ac4ad547c0d03922', '2026-07-25 10:59:31', '2026-06-25 03:59:31', '2026-06-25 12:01:12'),
(30, 66, '5379ad3a37230e14bceb1117fbfbe73abb8b64b9d5d95ad9857803a6804e0a07', '2026-07-25 11:02:07', '2026-06-25 04:02:07', '2026-06-25 11:02:07'),
(31, 55, 'ced2e60b26b768089a5830ad26fcdc878a90e31aec23daec831a4f07f17d1b9f', '2026-07-25 11:04:09', '2026-06-25 04:04:09', '2026-06-25 11:06:34'),
(32, 59, 'de27e957c25b94ac2a427de534f2757c1ea4677685cbf87ca04e2ec1eec4d24c', '2026-07-25 11:06:44', '2026-06-25 04:06:44', '2026-06-25 11:06:44'),
(33, 59, '87f222fec76fb44afa03c1f6d7f46bd23beb641ffbda2d8fd92b00ed3bbaae5a', '2026-07-25 11:09:17', '2026-06-25 04:09:17', '2026-06-25 11:14:21'),
(34, 55, '9219bbbc8bb93a51c46fbdf13ba20660fd0aa5e1eb4fcef250a0d70fe6508731', '2026-07-25 11:14:29', '2026-06-25 04:14:29', '2026-06-25 21:31:52'),
(35, 67, '3948233986228913780f9ce8b693b74425af66389844d36c69ce50275f4d9ddd', '2026-07-25 11:22:15', '2026-06-25 04:22:15', '2026-06-25 11:22:15'),
(36, 64, '84b024c1fb5086502431ca1f1cfffda75e4aca80d57f8dea90817917635e4da5', '2026-07-25 11:23:52', '2026-06-25 04:23:52', '2026-07-05 13:38:11'),
(37, 61, 'c9d580b2dfe351cfc99a818d888cecb76b0a773bbe168fd9fce1d938c2b4c396', '2026-07-25 11:50:49', '2026-06-25 04:50:49', '2026-07-06 10:46:14'),
(38, 68, '03c8a1793876fadd4c8189e153e4b991e1c6f907879572632690c4b51f1228d6', '2026-07-25 12:00:05', '2026-06-25 05:00:05', '2026-07-01 20:30:08'),
(39, 57, 'db121ff20aa2f3d6f03f1d9c2e1a4b21d42f886ee9cd2f6029128a00da9d2d63', '2026-07-25 12:01:20', '2026-06-25 05:01:20', '2026-06-25 12:39:46'),
(40, 69, 'd16856aaf939cbd862435d87de8990eef30e0c8a7dc99e314d30931d0ea280e3', '2026-07-25 12:09:42', '2026-06-25 05:09:42', '2026-06-26 19:43:48'),
(41, 70, '68849aed973c011af57768502ae9ea554c64a3923f246c65e95fc5c4340f5ee2', '2026-07-25 12:35:16', '2026-06-25 05:35:16', '2026-06-28 10:23:15'),
(42, 55, '3776756dbfcd2d9c143397f96e958e04794cc5af034aa0fc1944c616721210e2', '2026-07-25 12:39:51', '2026-06-25 05:39:51', '2026-06-25 12:49:54'),
(43, 83, '5937dfb234fded0b7018458f5ee9247c66de35c2bde5194d4a6e947b2de2f342', '2026-07-25 12:48:41', '2026-06-25 05:48:41', '2026-07-02 13:42:00'),
(44, 108, 'f7f9b01b3e23e85b0f2c4c1b186712c09791a308104c1b69f9f558790957f063', '2026-07-25 12:51:17', '2026-06-25 05:51:17', '2026-06-25 12:51:17'),
(45, 108, 'e81bcb17f2ccfa16b0d6a06820891b7be2b89af9982e2c0a5ff2a714e2881858', '2026-07-25 12:51:18', '2026-06-25 05:51:18', '2026-06-25 12:56:42'),
(46, 55, '625848d40f6fdfab2944c08c3ca7c88c1b3fbf80be4b915a7c67505af8c49051', '2026-07-25 12:56:45', '2026-06-25 05:56:45', '2026-06-25 15:27:41'),
(47, 109, '26cf8a26c6ad6a004659bc8bc81e672f22186571ffdbb955afea166acee8c452', '2026-07-25 14:17:29', '2026-06-25 07:17:29', '2026-06-25 14:38:37'),
(48, 110, '5f42774d3cb54d4cc592991d5206d24068c67d930d8b29db348a90d10ff74d6a', '2026-07-25 14:30:41', '2026-06-25 07:30:41', '2026-06-27 18:51:27'),
(49, 111, '8f17b2fd45f45c6b9045ddafbfc193b7f048f465339726fe465d84019a95e359', '2026-07-25 14:50:44', '2026-06-25 07:50:44', '2026-06-25 14:50:44'),
(50, 111, 'b1c6aada99fbf525e8953f845751e16d76245971d2267b0b8f6207d0d2cc170f', '2026-07-25 15:12:46', '2026-06-25 08:12:46', '2026-06-25 15:12:46'),
(51, 112, '075011609266a3f3b36f16e29ca56c48e784214d9bf79c5cfa349ac52f64d395', '2026-07-25 15:26:39', '2026-06-25 08:26:39', '2026-06-28 21:01:40'),
(52, 55, '8e034ec71dcd0350fdcdc59db2e03430d709ab865f8edabae46f2cf2e545f5ec', '2026-07-25 15:28:55', '2026-06-25 08:28:55', '2026-06-25 15:31:35'),
(53, 57, '5e4ff89776c5f492dad4f566557d0791d7c54695ec153f152863b0c0ddd2643c', '2026-07-25 15:31:43', '2026-06-25 08:31:43', '2026-06-25 15:31:43'),
(54, 108, '590619b4adb096082da9e24f28b0d7d744357ce17a4f483cf3d540c3471a17a7', '2026-07-25 15:31:47', '2026-06-25 08:31:47', '2026-06-25 15:34:20'),
(55, 55, '7201c45a5a853137b7b5c79ac93e020bc009bf3d4b284a39df8b207d9dbb9241', '2026-07-25 15:34:25', '2026-06-25 08:34:25', '2026-06-25 18:16:32'),
(56, 55, 'd3dbda2b5ede6b80acdab849f6290a11e56d0b97fa3cdd7ef244f59deee30dfc', '2026-07-25 15:34:53', '2026-06-25 08:34:53', '2026-06-26 11:05:35'),
(57, 70, '473d92db194623356618a66614b8b537451bf5cee24616987cd3552a95d2f8a9', '2026-07-25 16:07:11', '2026-06-25 09:07:11', '2026-07-06 16:25:21'),
(58, 111, 'ff7cd9dae33ff123642f53deadf33f2f1b16dfde1f329eca02d67b9812daabb3', '2026-07-25 17:45:23', '2026-06-25 10:45:23', '2026-06-25 17:45:23'),
(59, 57, '9e8dddbcc183636a416de8da32b203b1942152ce3a4d392719983de645dc5b61', '2026-07-25 18:16:35', '2026-06-25 11:16:35', '2026-06-26 18:57:02'),
(60, 113, '0c82019b31d9f085ab1b24c040b9c7f91bed532d342839a38e9a5927539c6034', '2026-07-25 20:25:32', '2026-06-25 13:25:32', '2026-06-26 07:37:38'),
(62, 55, '6307c51eb8926d065259743547e265aac42a5c65c50211ca433a9f3089671ef5', '2026-07-25 21:41:10', '2026-06-25 14:41:10', '2026-06-25 21:52:23'),
(64, 115, '6a29c5208bcca3e57a68810500afbf1174a57a5220ef4c51484248ac96fadb44', '2026-07-26 07:48:01', '2026-06-26 00:48:01', '2026-06-28 13:55:27'),
(65, 65, '5ee59007b471929c2424b9c2449bc03668830d44c80ff49ff8009c2f70a25bf4', '2026-07-26 08:44:38', '2026-06-26 01:44:38', '2026-07-02 10:50:50'),
(66, 55, 'd2dd6c012d24924b5e691c6b2db029b413ef5130a1f1a8230793a5759d945355', '2026-07-26 11:07:06', '2026-06-26 04:07:06', '2026-06-27 16:00:25'),
(67, 116, '15123ddd6e8822b07136a84e8ff2ff5633fec71b0b63ad0f9fe5741b408feceb', '2026-07-26 11:49:38', '2026-06-26 04:49:38', '2026-06-26 11:49:38'),
(68, 117, '1558ce1ed39497c44d8b84f3f02e7767c62e974e55255b59070bea4789a17c51', '2026-07-26 17:03:12', '2026-06-26 10:03:12', '2026-06-26 17:08:40'),
(69, 55, '7954fcd4b02a394708b322eb7681b8c78e2f606713b4b30929e8d8e897ff7e2b', '2026-07-26 18:57:22', '2026-06-26 11:57:22', '2026-06-28 12:34:45'),
(70, 112, '5c06d995fd214c792feb0bcdbc1300548897743a7a182c4fdb43b20f423717dd', '2026-07-26 19:12:11', '2026-06-26 12:12:11', '2026-06-27 14:18:27'),
(71, 118, '0c65865fac4c76ec8ab988431ad8b20b30db283820f132a717a5fca7999ec5c1', '2026-07-26 19:57:46', '2026-06-26 12:57:46', '2026-06-27 00:57:47'),
(72, 119, '8e9aab93324cc7e2cb8f25109abd1446d8e90f950035280d4be653ef83def8b6', '2026-07-26 21:31:48', '2026-06-26 14:31:48', '2026-06-26 21:31:48'),
(73, 119, 'e0014dbfed61a110dd684a5d2305e29c36a0d4f4f7f915c74b99fa41b4a1156e', '2026-07-26 21:32:32', '2026-06-26 14:32:32', '2026-06-26 21:32:32'),
(74, 55, 'd25eb002c72a2fb97b9f3f8a4c997ba9ce8c9e6cc1069e24763022aa4fdefca7', '2026-07-27 05:54:18', '2026-06-26 22:54:18', '2026-06-27 05:54:18'),
(75, 55, '27fc86823e63e764a41c4cf165609dc9d3847f2f29a60aa96b5e5122903b8568', '2026-07-27 05:56:40', '2026-06-26 22:56:40', '2026-06-27 05:57:07'),
(77, 55, '287f75e17812c3643782fa498766b77c1f36184d4ec25a0041a1d870c087fe6c', '2026-07-27 06:10:07', '2026-06-26 23:10:07', '2026-06-27 06:10:56'),
(79, 55, 'fc206b4d0be5988f8767b2723924f67c3f300f5154fad9700fa9d79edbefab59', '2026-07-27 06:29:16', '2026-06-26 23:29:16', '2026-06-27 06:37:32'),
(80, 121, '97807a8b27ddb86a37b2a322132dd4b7aa4ffea83b2bc2d7e1f23f10947474e8', '2026-07-27 09:58:44', '2026-06-27 02:58:44', '2026-06-28 16:15:42'),
(81, 55, '1e388ac1562c342f115da5d05eeee7898daf0d10c42cb0c46d74662e12d6218a', '2026-07-27 18:13:31', '2026-06-27 11:13:31', '2026-07-04 00:30:37'),
(82, 122, '08e59702eb20ed44e409c0e0bab88b62a9cec10671b341043dbac4a4ba353fb6', '2026-07-27 18:19:04', '2026-06-27 11:19:04', '2026-06-27 18:19:04'),
(83, 122, 'd58f4f7bf04d9c862e7b8bf181229862d3a7e081268cb144de18fd56c7fdd26e', '2026-07-27 18:19:04', '2026-06-27 11:19:04', '2026-06-27 19:34:35'),
(84, 59, '77c88ad7b27ccefd02df979f702d227419bbf17062655d2ce3387c32b09c85ab', '2026-07-27 18:41:10', '2026-06-27 11:41:10', '2026-06-27 23:59:53'),
(85, 124, 'a40a6755d3e947f478ce2c315fb3af6f9ce69f87d17dfc5da606467c4be9ec95', '2026-07-27 19:14:46', '2026-06-27 12:14:46', '2026-06-27 20:00:17'),
(86, 123, '1c541448828608800df47a41a3c885b174c7a661534146788cca7ec108d305b6', '2026-07-27 19:14:50', '2026-06-27 12:14:50', '2026-06-30 07:40:34'),
(87, 124, '492f74948df5e39e0a53da27678a53e55aafad5fdf9eca6b98333184f7b49d4f', '2026-07-27 19:51:06', '2026-06-27 12:51:06', '2026-07-06 15:13:02'),
(88, 55, 'f5644c9cc6bb223201391133c8814534af0d6152c3575753e73b8b9278481862', '2026-07-27 23:59:55', '2026-06-27 16:59:55', '2026-06-28 07:19:30'),
(89, 117, '09254c84312a723fc9373326c6c31d206742e7765e59a2cd97b981d4f47dd72f', '2026-07-28 02:25:57', '2026-06-27 19:25:57', '2026-07-01 20:19:59'),
(90, 55, 'a87507e5b7a45e7db1932d15694b803955f58775008d7c27831a219fe01b6dd8', '2026-07-28 06:35:19', '2026-06-27 23:35:19', '2026-06-28 06:35:45'),
(91, 125, '805401f8721f8d7f36b7d2cd82e21aa449f78b188bd35a5110678eec4da0e298', '2026-07-28 06:35:49', '2026-06-27 23:35:49', '2026-06-28 06:47:29'),
(92, 55, '17c6c9c8b1191c14a7b4ff3c45a022ad5ddff384c76a328c56369c2ec19831bd', '2026-07-28 06:47:34', '2026-06-27 23:47:34', '2026-06-28 06:48:15'),
(93, 125, 'a10fd1867fb0d5e0d3c05912567b24f7a8efebd094ce4b3d318caf964a38d779', '2026-07-28 06:48:21', '2026-06-27 23:48:21', '2026-06-28 06:48:27'),
(94, 55, '13f006df42f5df795048043d595074d1667c907397afbe4342800cd0d5bf3e46', '2026-07-28 06:55:39', '2026-06-27 23:55:39', '2026-06-28 06:57:52'),
(95, 125, '9e784ef939c2c1c90ec34adfdb351d7fea8899b6cbf153f38f367de1fc1ae8c7', '2026-07-28 06:58:11', '2026-06-27 23:58:11', '2026-06-28 07:00:13'),
(96, 55, 'eb947bdc7063768b46b95d448b3e2b735cbbfc7fa37ec506ca0f394552f2482f', '2026-07-28 07:00:19', '2026-06-28 00:00:19', '2026-06-28 07:02:18'),
(97, 125, 'ec0907d0a118c427cfbfc48ca6690d37a9a4fb3693166a1dbb08e7fc72e54649', '2026-07-28 07:02:22', '2026-06-28 00:02:22', '2026-06-28 07:10:40'),
(98, 55, '765a9b693c05e0d11e26d871ba90c3ce87a07ac67c79c5354725ae42c66e2a83', '2026-07-28 07:10:47', '2026-06-28 00:10:47', '2026-06-28 07:11:59'),
(99, 125, '07380aad88ea576ba27d3c5694a07ad9cca15b5aadf0c7cc1a18d26e1a0bbfb3', '2026-07-28 07:12:02', '2026-06-28 00:12:02', '2026-06-28 07:12:39'),
(100, 55, 'a944589316fb82930c84e8983422327ec3e5fdc457f2a0eb726c4659487c514f', '2026-07-28 07:12:55', '2026-06-28 00:12:55', '2026-06-28 07:13:43'),
(101, 125, '075872a9f2db23e70dfe41b131bccede17eb2cedfd0a09c81e2f8b2b8f3719f0', '2026-07-28 07:13:49', '2026-06-28 00:13:49', '2026-06-28 07:14:15'),
(102, 55, 'fdf0382fedbc9fd58dc4e011affa612bf20f61bec8bc380dcdb19a435e3d283c', '2026-07-28 07:14:21', '2026-06-28 00:14:21', '2026-06-28 07:14:42'),
(103, 125, 'b393ae2e49f0fcb3a9653db15262b6fde94444a43dcbd705bab8d15c5fc9105b', '2026-07-28 07:19:17', '2026-06-28 00:19:17', '2026-06-28 07:21:56'),
(104, 125, 'df7891a378e1b86b8f94b51960254d3b0d920c6c04c9fbef769c43e948c48eb7', '2026-07-28 07:19:32', '2026-06-28 00:19:32', '2026-06-28 07:32:42'),
(105, 125, 'e2bc954782770e9d38a4cf0236c986c9b3d77a321fe62554872a46093fcd53bb', '2026-07-28 07:23:49', '2026-06-28 00:23:49', '2026-06-28 07:23:49'),
(106, 55, '9bd02831c9c9a5fec47a4d97348c6f164eb6c1c551dc8a29ee23739db0e8a5e2', '2026-07-28 07:32:44', '2026-06-28 00:32:44', '2026-06-28 07:33:16'),
(107, 55, '55acbe9a05dd3129edf5cdbcef333451c5cfb916c4fb3c3f81655639f71462ef', '2026-07-28 07:33:41', '2026-06-28 00:33:41', '2026-06-28 07:33:41'),
(108, 56, 'fe57078d69e616cb5d0dddccf9a54a2804e06206abc6ffbbe37bfd7966ff4319', '2026-07-28 10:49:46', '2026-06-28 03:49:46', '2026-06-29 09:45:03'),
(109, 108, '0e9569fb60fe94d4c5104a50efb89784c3c0b6d900f31525c46aae68c303e010', '2026-07-28 12:34:49', '2026-06-28 05:34:49', '2026-06-28 12:36:38'),
(110, 55, '652c5dca9335112ff7c37266192c45a66fdf59b623bbafb99130882bbd2b697f', '2026-07-28 12:36:42', '2026-06-28 05:36:42', '2026-06-29 09:04:18'),
(111, 55, '4ca11b610d84cb860cd7f5a9dc205349d7104acad60e2b2148070ac3d1326da0', '2026-07-28 13:28:57', '2026-06-28 06:28:57', '2026-06-28 13:28:57'),
(112, 126, '03a8ed87ce575bb39b8469b42a62eda8240d1d665ab11cc1f382a8ed437c5f34', '2026-07-28 15:35:53', '2026-06-28 08:35:53', '2026-07-02 19:21:32'),
(113, 127, '6abee6b6a0fb56b1a587ae8c58e1e7ccdbfebe8d619060df20d1d36b5029d20f', '2026-07-28 21:40:46', '2026-06-28 14:40:46', '2026-06-28 21:40:46'),
(114, 128, 'b2e73a6402c15b9a222380ed6e5001ed2c251894b151483368b34131d754c87b', '2026-07-29 09:01:27', '2026-06-29 02:01:27', '2026-07-05 14:02:32'),
(115, 57, 'db0a1b8b6a9bd590a8f3e355b3892823510d0510fd010cadea75d4e5c08e88e9', '2026-07-29 09:04:30', '2026-06-29 02:04:30', '2026-06-29 09:24:29'),
(116, 129, 'a9be0fe74373ce059a43622fa2313686aca4b31de34d984ed0d1bb0b06c53167', '2026-07-29 09:15:44', '2026-06-29 02:15:44', '2026-07-06 11:25:38'),
(117, 108, 'a281900acfd49f0b59597f84f8121109477478476b71f2a945b252dbc9174068', '2026-07-29 09:24:31', '2026-06-29 02:24:31', '2026-06-29 17:20:23'),
(118, 125, '5d2674a039a805166c4a43c039428cfd3cc3fe19ad9a2de3e2096dd96cffa43c', '2026-07-29 09:45:06', '2026-06-29 02:45:06', '2026-07-01 16:20:07'),
(119, 125, '2ca5b1ee5358e695b8d80cbc37bad29bd934660193d1d6b25ca526e2d39c7b48', '2026-07-29 10:09:27', '2026-06-29 03:09:27', '2026-06-30 07:54:45'),
(120, 130, '8e0a1491ff4048035759a524f014ec4145a90689d666917984afbc1e1e1eba3f', '2026-07-29 14:22:13', '2026-06-29 07:22:13', '2026-06-29 17:16:03'),
(121, 131, '2518e9d4bb09786cbe4a58f0686dead5286f0de1840ab412f615a7e02630aafe', '2026-07-29 16:53:21', '2026-06-29 09:53:21', '2026-06-29 19:30:37'),
(122, 55, '78ee1b964ee75defdfd2198c645786907bdb1f212741236e71cdd4c2265de5a4', '2026-07-29 17:20:27', '2026-06-29 10:20:27', '2026-06-29 19:57:29'),
(123, 132, '95e0265b8e47f866a7e694ad5c0ada7fd2a960fb3548deca5c565772e7b6295a', '2026-07-29 17:26:34', '2026-06-29 10:26:34', '2026-06-29 17:26:34'),
(124, 108, '2f7d5565d5780b4b78602a4913bbccb212d10f5581288586da073270ec1f4f60', '2026-07-29 19:57:32', '2026-06-29 12:57:32', '2026-06-30 07:16:25'),
(125, 55, '3df46701e47de96fe391edb575df5d9ea0722dece1b4953ed744ddfe175c3b06', '2026-07-30 07:16:29', '2026-06-30 00:16:29', '2026-06-30 08:45:48'),
(126, 55, '054b565498628b84798e5585ecc3d20ed731ddfcd1f468c86c8032807e2a8169', '2026-07-30 07:54:52', '2026-06-30 00:54:52', '2026-06-30 07:54:52'),
(127, 133, '59e26748533bd15e27a7adbbdff30ce0a1351d1b5897cf6de85c5ecb58c3d592', '2026-07-30 08:32:00', '2026-06-30 01:32:00', '2026-06-30 08:58:59'),
(128, 57, '437399e2f5402687b48fc7d344de33c5662d6027410b01d4c50fb5dcde9d591f', '2026-07-30 08:49:51', '2026-06-30 01:49:51', '2026-06-30 08:56:17'),
(129, 55, 'a272d41b7e1d21fdef3297b0b187b9454f69eeeda65bb81de5c1e54be66c8dac', '2026-07-30 08:56:24', '2026-06-30 01:56:24', '2026-07-01 12:22:45'),
(130, 134, '34ad9d89e10c956cc63e1037551ca1520cf111ad52a7874e118fc4fc3e5a37e0', '2026-07-30 11:58:34', '2026-06-30 04:58:34', '2026-07-06 14:37:45'),
(131, 134, 'e5b52732918437d2a3f850fb9f1697648342ae5076b0159fb8f65a658fdf164c', '2026-07-30 12:28:05', '2026-06-30 05:28:05', '2026-06-30 14:06:55'),
(132, 135, '5dab2df65aacab866298aba64b813f95b0df346a94df5c6f6b43442222bb8506', '2026-07-30 15:14:26', '2026-06-30 08:14:26', '2026-06-30 15:40:50'),
(133, 137, 'c93b8f7cfa135132d8e11e504e5125bdc8c65923dd5653bb3fdbef0f0fb73c19', '2026-07-30 18:29:13', '2026-06-30 11:29:13', '2026-06-30 18:29:13'),
(134, 138, 'ae6cc04561f431be1f10ba9873f32c96d3f385c054118807930ce7fbf98d10bf', '2026-07-30 20:05:10', '2026-06-30 13:05:10', '2026-07-06 16:20:41'),
(135, 117, '5b9f27f013db18ee8dff7c4aef16b615f7e73d1210c1fa5faeddffc864ede867', '2026-07-30 20:27:16', '2026-06-30 13:27:16', '2026-07-01 06:37:36'),
(136, 55, '66019ee347c13768730ac256d824f5cd52dbdb3454ba8d7d54cd463dc8aafcd5', '2026-07-30 22:11:06', '2026-06-30 15:11:06', '2026-06-30 22:11:06'),
(137, 108, 'c40aaeacb30ef26a3fbca071a97e308a2a176fa2d393584bb25f434da9e2e3b3', '2026-07-31 12:22:48', '2026-07-01 05:22:48', '2026-07-01 12:25:00'),
(138, 55, '9806898045b7d97ee30670090f3d50f2f6fc3f66b04ef99510a02b04537a3aa9', '2026-07-31 12:25:03', '2026-07-01 05:25:03', '2026-07-01 12:29:15'),
(139, 57, 'd1699b52b4b6df1266224121008c8644e9ec6d7f064a23aeabc1c092331d9a5e', '2026-07-31 12:29:20', '2026-07-01 05:29:20', '2026-07-01 18:22:03'),
(140, 139, '69cabe2efccafee7e01223377216bc38434a01025b6663111dc4f488e37465e5', '2026-07-31 13:01:17', '2026-07-01 06:01:17', '2026-07-05 13:49:17'),
(141, 140, '20dd623ff3617566a3f7f54039b147341fce7ba0b017f89c0c12df7823ac21b6', '2026-07-31 14:01:17', '2026-07-01 07:01:17', '2026-07-03 12:12:10'),
(142, 139, '7cb9ee47bc03aa644b96d5b84755bde9fed6352f92b7b9dd9b977921a809db00', '2026-07-31 14:41:31', '2026-07-01 07:41:31', '2026-07-01 14:41:31'),
(143, 55, '75f6faa19319bdee9e316ebeb500478e943d4c0f6ac91ab57ac3b866d41ab5d9', '2026-07-31 16:20:10', '2026-07-01 09:20:10', '2026-07-05 13:48:00'),
(144, 55, '1899823cbd77ab01ba06d8fd2ca32a97241dcd84f0bd764a71d66a0d34c6b4bc', '2026-07-31 18:22:09', '2026-07-01 11:22:09', '2026-07-01 18:22:09'),
(145, 108, '2568d41452931f4359b914304cd04c7e6f1e1da131a8ce0bd629f617ee64255a', '2026-07-31 18:22:22', '2026-07-01 11:22:22', '2026-07-01 20:18:04'),
(146, 55, '581d87eaf9cf88482d9db254710ae7c0a3a0b3a829dd26221a9d13ef33509ec0', '2026-07-31 20:04:50', '2026-07-01 13:04:50', '2026-07-01 20:10:23'),
(147, 125, '63588f98955931a872e1e6558100648d4cbdb848ec1e2581b2acb44f29e5f03f', '2026-07-31 20:10:34', '2026-07-01 13:10:34', '2026-07-01 20:13:09'),
(148, 55, '302035d1cacc19fe07fed2c1d44a35bf6939f6a9cdbbabcd546fa27ee2faa08e', '2026-07-31 20:13:22', '2026-07-01 13:13:22', '2026-07-01 20:27:36'),
(149, 55, '89f396cc3065666d8f5bbc7786cbb8e4d9684514fc9a224ed0207b64f09cb099', '2026-07-31 20:18:07', '2026-07-01 13:18:07', '2026-07-02 10:14:12'),
(150, 125, '41b3b019f9ca0a545ed8224b165c025118bd6c8e976b95f76503018b76a26e1d', '2026-07-31 20:27:44', '2026-07-01 13:27:44', '2026-07-02 11:31:22'),
(151, 141, 'b31acec9f2ea64b045dff517ca91532cfc5d0f5a6e54c59dda8c3c76a69c04ab', '2026-08-01 08:54:49', '2026-07-02 01:54:49', '2026-07-03 13:39:58'),
(152, 142, 'c5c6d0563a701cea13481db7a097ea429a7ac7cd4c7782a3f2d7fc90f6a4695d', '2026-08-01 10:12:57', '2026-07-02 03:12:57', '2026-07-02 10:12:57'),
(153, 108, '4d6c29329fc9403afdea7511370abe0554a65e699557fda1c5b7f1655944a1d4', '2026-08-01 10:14:16', '2026-07-02 03:14:16', '2026-07-02 10:25:15'),
(154, 55, '29db1a6f735f76100b51bd370c7229bf409427b1ac09920ed78303cf2f845ecf', '2026-08-01 10:25:37', '2026-07-02 03:25:37', '2026-07-02 10:27:03'),
(155, 108, '8ba38af52b8a8a631fae47357fed1c32f18ab5042b1cc6874eacb2943cc81d67', '2026-08-01 10:27:08', '2026-07-02 03:27:08', '2026-07-02 13:22:33'),
(156, 143, '63c1b17a055920ae848a1ecc1bad7d952d07563d4b60cb30bc5b823dfe36da6b', '2026-08-01 10:52:42', '2026-07-02 03:52:42', '2026-07-02 10:52:42'),
(157, 55, '66f916e729d62fb4d37fc98aaa401fe029ea567deedc6b23e0c675e94e353c1a', '2026-08-01 11:31:27', '2026-07-02 04:31:27', '2026-07-02 12:05:25'),
(158, 55, '44ad8542509a3675f2c68bd689c45882c1071b958c97011239e71e0b2120bed1', '2026-08-01 12:09:32', '2026-07-02 05:09:32', '2026-07-02 12:09:32'),
(159, 55, '4f89f7af7a51fcd19bcdcaf0527389b52f7499fa62017dcb69f7ac1c8c442f17', '2026-08-01 12:19:00', '2026-07-02 05:19:00', '2026-07-02 12:19:06'),
(160, 55, '85521de1cd6805944617e3636d8637462894ee4c666a23f2b9601f86ccaec07c', '2026-08-01 12:23:59', '2026-07-02 05:23:59', '2026-07-02 12:28:36'),
(161, 55, '767191a8a9651d02786f49eccc46deb6daa36153daeadc2f294cc6e87ef3a9c4', '2026-08-01 12:36:40', '2026-07-02 05:36:40', '2026-07-02 12:36:40'),
(162, 55, 'cc2fd60b530ce4314c99930185fe86b69999c152969f17341425f39d5f2a39ae', '2026-08-01 12:40:02', '2026-07-02 05:40:02', '2026-07-02 12:40:02'),
(163, 59, 'edf5a5847213f42d43db5f5b4ffd0b14a62824719b0dab78cac2f5ebd5fb8a17', '2026-08-01 12:41:51', '2026-07-02 05:41:51', '2026-07-02 12:42:03'),
(164, 55, '71d38fac69448acaad533c017ebe9698f03949b46424ece0a990084bc578ecc2', '2026-08-01 12:42:08', '2026-07-02 05:42:08', '2026-07-02 12:42:08'),
(165, 59, '55b65fd6ea1c40c6db1e19bd99d838adc6e3b4f60c60fc315928ac4657c8563d', '2026-08-01 12:42:34', '2026-07-02 05:42:34', '2026-07-02 12:42:45'),
(166, 55, '8b320acc2699ef56ddd85a3e6b11946cabcad7ac8cf3cda2ececddcc1fa77659', '2026-08-01 12:42:51', '2026-07-02 05:42:51', '2026-07-02 12:42:51'),
(167, 59, 'cd7823cc5bcde6cb1bc9c2fbc79df2cbaed93294e00c7dea51936d1850da8f07', '2026-08-01 12:43:52', '2026-07-02 05:43:52', '2026-07-04 07:40:28'),
(168, 144, '515dae79ff8527110a6a943cf8f2eb2fa68de998cd514819c38977f86be782b3', '2026-08-01 13:04:35', '2026-07-02 06:04:35', '2026-07-02 13:04:35'),
(169, 57, 'dc63de9900207faf152795f5f1779169e4098fbe2d45f81ff1ce6ed3842aec00', '2026-08-01 13:26:01', '2026-07-02 06:26:01', '2026-07-02 13:27:33'),
(170, 55, '1e32ab8e4bbcb7e34b94b09a6ca3a6c52c6407abe5b764ad94b830610cb3bdba', '2026-08-01 13:27:36', '2026-07-02 06:27:36', '2026-07-02 22:20:15'),
(171, 145, 'a1218feb319549463becb31c5529aa2e323ffde70a69af6f9f8db2ea55221e63', '2026-08-01 17:44:39', '2026-07-02 10:44:39', '2026-07-04 19:50:21'),
(172, 146, '530f51f5e7f1660c6ccba99daf0e17568409a5ee942a1fc70a2c4b25cd23683b', '2026-08-01 18:16:06', '2026-07-02 11:16:06', '2026-07-03 09:55:37'),
(173, 147, 'b047461600b369ab02253956a6cae2a19c8720941b420bb1319c141b4ba373bd', '2026-08-01 19:54:33', '2026-07-02 12:54:33', '2026-07-03 08:52:47'),
(174, 148, 'a9744b2b90cdb72fd928578084471ce7acd51e541d4075c48e24f29cddf721dd', '2026-08-01 19:57:34', '2026-07-02 12:57:34', '2026-07-02 21:27:18'),
(175, 108, 'd4e52ac7735f22d4cbfc500839e8953256d31b90fbec206ff3143a8d6f4be1af', '2026-08-01 22:48:44', '2026-07-02 15:48:44', '2026-07-03 07:41:26'),
(176, 149, '867b806d74631f8114c14e742d3b65bb7efbfb200175a82d7c6c181cf81564ee', '2026-08-02 02:01:59', '2026-07-02 19:01:59', '2026-07-03 02:01:59'),
(177, 55, '1aeff92916647ac98cda145ea94bb8d029ac8778a34ab5dec1f5e85b4bb51fed', '2026-08-02 07:41:29', '2026-07-03 00:41:29', '2026-07-03 08:05:51'),
(178, 115, 'aab5022fd60db90c5542941f5ea90f81bc910f8a3e9fa265b737246f02a2bc60', '2026-08-02 08:15:19', '2026-07-03 01:15:19', '2026-07-04 09:01:22'),
(179, 108, 'dc1085812a13660e397e2fcab0fac4cafdb0ac3dc6240411de2f2144bd425030', '2026-08-02 10:31:34', '2026-07-03 03:31:34', '2026-07-03 11:13:19'),
(180, 55, '11e07fc060939a08b23864a690257effab868978b350abc2731e6e4fa5c14533', '2026-08-02 11:13:22', '2026-07-03 04:13:22', '2026-07-03 11:19:01'),
(181, 108, '9ddcab30031fdf8ad71565cf37f9cc801c36f85c7623cc78f053ea454eebb699', '2026-08-02 11:19:05', '2026-07-03 04:19:05', '2026-07-03 12:14:20'),
(182, 55, 'c3f340c0280fb0fa7bd40489b3448429d1f39b7ef4855f6518949e100f1091bc', '2026-08-02 12:14:24', '2026-07-03 05:14:24', '2026-07-03 20:39:13'),
(183, 150, 'f921b36b36d7f7c11eb82f84126258c8d297bd27356d5b5eb99867052f28fa44', '2026-08-02 17:02:24', '2026-07-03 10:02:24', '2026-07-05 22:25:19'),
(184, 144, 'bd530b7c62f0c896992920c16ab48b7501936ba930db099aa282c65d4bb9d80b', '2026-08-02 17:08:10', '2026-07-03 10:08:10', '2026-07-05 19:24:27'),
(185, 68, 'b6a194228105c02bcd84725377c7ca98904b7d0bc0195f25b46083d40b8856ce', '2026-08-02 18:18:21', '2026-07-03 11:18:21', '2026-07-03 18:18:21'),
(186, 108, '6a5ff2c7825065307bae6da30a89c1be7461bd831d1405c5842f658b88aae6e3', '2026-08-02 20:39:17', '2026-07-03 13:39:17', '2026-07-03 22:28:38'),
(187, 55, 'bbc88f011fc9314d8a593a065881f648801200d08216739de22659258c6248c1', '2026-08-02 22:28:43', '2026-07-03 15:28:43', '2026-07-04 13:37:37'),
(188, 125, 'e962b0426407f8092d6df11f132333e4f9ce687996e792254fe7ee0106163ee4', '2026-08-03 07:40:35', '2026-07-04 00:40:35', '2026-07-04 07:49:44'),
(189, 55, '4dc5f31adc1847a8f607b9bb2db3ca964dd8e8c12563630ddd7712453d0ffabd', '2026-08-03 07:49:52', '2026-07-04 00:49:52', '2026-07-04 10:25:30'),
(190, 57, 'c3225f3d41d5862369edc8793f9395feec85a29a7e0b38bd1b8d2866d27e58c9', '2026-08-03 13:37:39', '2026-07-04 06:37:39', '2026-07-05 08:30:50'),
(191, 151, '0f9d4cf852234dc2eda24a076858552e4604edf912f30c62e1364bb29907044b', '2026-08-03 13:48:36', '2026-07-04 06:48:36', '2026-07-04 23:11:17'),
(192, 152, '7bf85571f7529e1c0f37a44143fa373a856dbf086c35c14ac5475ac62e394186', '2026-08-03 21:16:44', '2026-07-04 14:16:44', '2026-07-04 21:16:44'),
(193, 55, '3b0c22dc9c3906cd846e13970b44057154e754f04751867ea0eea2c5d5bcb981', '2026-08-04 08:30:54', '2026-07-05 01:30:54', '2026-07-05 12:40:45'),
(194, 153, '1f9b7a27974f7a31c6249d713264653f60d4e50092695e686104935ae96320b2', '2026-08-04 11:10:20', '2026-07-05 04:10:20', '2026-07-05 11:10:20'),
(195, 153, 'c1ef5dd4943c487b61a009b7aba7d8925a0ae9344603e6b065c44a62c21ca052', '2026-08-04 11:15:21', '2026-07-05 04:15:21', '2026-07-06 16:25:05'),
(196, 154, 'ad2a5128ff691b3cef1f968bd6ad7c34991a61ff004c4f062453f4fecc82d635', '2026-08-04 11:45:02', '2026-07-05 04:45:02', '2026-07-06 16:42:26'),
(197, 108, '6fb48b59b436cd340f5d49596ad9799cffe4bc6a367d170110610aacd230a583', '2026-08-04 12:40:49', '2026-07-05 05:40:49', '2026-07-05 17:29:43'),
(198, 57, '481a8f9a17a6e7581fe2b9d9cfb622c9b10d5122dbddae7ac83f92dfef9f2be1', '2026-08-04 13:48:13', '2026-07-05 06:48:13', '2026-07-05 13:48:13'),
(199, 59, 'd65afc54eb4a6d592dee74892de74167de725bef4c86a65f153c49d59d933545', '2026-08-04 13:48:13', '2026-07-05 06:48:13', '2026-07-06 17:51:47'),
(200, 139, 'b1d1e58f1dd817e74c72199327b196b70ebcc6fbb1725186345778242de15c65', '2026-08-04 13:52:44', '2026-07-05 06:52:44', '2026-07-05 13:56:53'),
(201, 59, 'b3618731bd46996db466ec246308a2184dcbb4764b624eb5309e7a0b874d8b53', '2026-08-04 13:55:24', '2026-07-05 06:55:24', '2026-07-05 14:04:15'),
(202, 149, '045db6037926d7b163f4d96bd152ad6d66a2c40bf97caf4540a5a9bb0fe77139', '2026-08-04 15:33:19', '2026-07-05 08:33:19', '2026-07-05 15:36:48'),
(203, 149, '02a666d56d094605a1f3e230cb64d2a6c0df53bca0f95120268d9fe3ca393ed6', '2026-08-04 17:14:40', '2026-07-05 10:14:40', '2026-07-05 17:14:40'),
(204, 149, 'ef4398c7dc24428058d00347f2bc0de58994ebfe6fa183756edd341fcc33fd5f', '2026-08-04 17:17:34', '2026-07-05 10:17:34', '2026-07-05 17:17:34'),
(205, 55, '619b73660574e6e0b5a47600612e08ffcf7cb4aa4c263f41fe6f88bcfac004c6', '2026-08-04 17:29:45', '2026-07-05 10:29:45', '2026-07-06 10:57:06'),
(206, 68, 'cac98dda743d787a255529d65ac62898cc9ff97954ec2084d86f1d5af35eb4cc', '2026-08-04 17:29:50', '2026-07-05 10:29:50', '2026-07-05 17:29:50'),
(207, 155, '1491e92a0e2224fc12885609f4a08d04fa84a70fe84f8e7724169ea7e646aae5', '2026-08-04 23:20:31', '2026-07-05 16:20:31', '2026-07-05 23:20:31'),
(208, 156, 'da283e8c24f4b7a31eeb3558a6ef2cd4134c500a7a21ca8be664eb39ca4454ac', '2026-08-05 10:54:51', '2026-07-06 03:54:51', '2026-07-06 10:54:51'),
(209, 108, '2d0fd0b65427fceeafb0b8020d796b994ae5f1cff73654892f65892118d8d9ff', '2026-08-05 10:57:09', '2026-07-06 03:57:09', '2026-07-06 16:55:20'),
(210, 57, '5023bfe0c137b02e6520b57fd5e8eaaf3fbb37e1e1453c2b980fbff31a8cd958', '2026-08-05 17:02:01', '2026-07-06 10:02:01', '2026-07-06 17:04:39'),
(211, 55, '43d3f3d79fafa95f0c0717b71e2f60235e3500e191bdb036df5d318e116a8a09', '2026-08-05 17:04:43', '2026-07-06 10:04:43', '2026-07-06 17:36:03'),
(212, 157, '121b25769f1f68131ec3846f615524a051719cb174d3a4a180fd48e6e2fe8c10', '2026-08-05 17:27:02', '2026-07-06 10:27:02', '2026-07-06 17:41:56'),
(213, 59, 'e1bda7e36b12feb20b545edf80a739a00a6408aa721645115d76de2b6e478577', '2026-08-05 17:51:55', '2026-07-06 10:51:55', '2026-07-06 17:51:55'),
(214, 55, '5040fea7f06a1aadc7aa6acc73c0ebb492ecacfceccf61d942ad61413961d78f', '2026-08-05 18:07:46', '2026-07-06 11:07:46', '2026-07-06 18:07:46'),
(215, 55, 'a480c5fa157dddd7710eec5a2dc51fafd5f29643bbe48a924d2c38fad1503724', '2026-08-05 18:11:54', '2026-07-06 11:11:54', '2026-07-06 18:11:54'),
(216, 59, '5716c70bff260c6f3a966f6574e7260021e03ab4acea5ac2654d2251f59f4026', '2026-08-05 18:13:45', '2026-07-06 11:13:45', '2026-07-06 18:17:17'),
(217, 55, '71586139fe170dd84917266f6bc14622f8f87bae602aca38a70adcc4967a30c5', '2026-08-05 18:17:20', '2026-07-06 11:17:20', '2026-07-06 18:18:09'),
(218, 59, '25fd43596c88387cefa3fb4903ae0a4a71baa1e2f0483fe67f815bd929e0fcfa', '2026-08-05 18:22:08', '2026-07-06 11:22:08', '2026-07-06 18:36:34'),
(219, 55, 'd8ea091f19a80808c27c933fa1671410a5ef325722049faf49387fa219667ff0', '2026-08-05 18:42:36', '2026-07-06 11:42:36', '2026-07-06 18:43:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `worker_tasks`
--

CREATE TABLE `worker_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `addon_id` varchar(50) DEFAULT NULL,
  `trip_addon_id` bigint(20) UNSIGNED DEFAULT NULL,
  `addon_name` varchar(150) DEFAULT NULL,
  `worker_action` enum('drive_link','none') NOT NULL DEFAULT 'none',
  `worker_id` bigint(20) UNSIGNED DEFAULT NULL,
  `slot` int(11) NOT NULL DEFAULT 1,
  `total_workers` int(11) NOT NULL DEFAULT 1,
  `task` text DEFAULT NULL,
  `status` enum('Tersedia','Diambil','Sedang Berjalan','Selesai') NOT NULL DEFAULT 'Tersedia',
  `result_link` text DEFAULT NULL,
  `drive_link` text DEFAULT NULL,
  `proof_photo_url` text DEFAULT NULL,
  `proof_photo_name` varchar(255) DEFAULT NULL,
  `completion_checked` tinyint(1) NOT NULL DEFAULT 0,
  `result_status` varchar(50) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `completed_by_name` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `worker_tasks`
--

INSERT INTO `worker_tasks` (`id`, `booking_id`, `trip_id`, `addon_id`, `trip_addon_id`, `addon_name`, `worker_action`, `worker_id`, `slot`, `total_workers`, `task`, `status`, `result_link`, `drive_link`, `proof_photo_url`, `proof_photo_name`, `completion_checked`, `result_status`, `completed_at`, `completed_by_name`, `created_at`, `updated_at`) VALUES
(1, 26, 39, NULL, 217, 'Dokumentasi Foto Camera', 'drive_link', 108, 1, 1, 'Kerjakan add-on Dokumentasi Foto Camera untuk booking ini.', 'Selesai', 'https://drive.google.com/drive/folders/1Z5Jfz6-3XNBg25kV4rM_O2rIo5M1Nknf', 'https://drive.google.com/drive/folders/1Z5Jfz6-3XNBg25kV4rM_O2rIo5M1Nknf', NULL, NULL, 1, 'completed', '2026-06-25 08:32:54', 'Zakkiatuz Zahrolazizah', '2026-06-23 13:28:06', '2026-06-25 08:33:14'),
(3, 31, 33, NULL, 179, 'Ojek Trip Goa Ngeleng Only', 'none', NULL, 1, 1, 'Kerjakan add-on Ojek Trip Goa Ngeleng Only untuk booking ini.', 'Tersedia', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-25 04:59:11', '2026-06-29 03:09:43'),
(4, 34, 33, NULL, 179, 'Ojek Trip Goa Ngeleng Only', 'none', 61, 1, 1, 'Kerjakan add-on Ojek Trip Goa Ngeleng Only untuk booking ini.', 'Selesai', NULL, NULL, NULL, NULL, 1, 'completed', '2026-07-05 23:05:38', 'Muhammad An Nizar', '2026-06-25 05:40:58', '2026-07-05 23:05:38'),
(5, 35, 33, NULL, 177, 'Camera Insta360', 'drive_link', 108, 1, 1, 'Kerjakan add-on Camera Insta360 untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-25 05:48:34', '2026-07-02 04:26:00'),
(6, 35, 33, NULL, 179, 'Ojek Trip Goa Ngeleng Only', 'none', NULL, 1, 1, 'Kerjakan add-on Ojek Trip Goa Ngeleng Only untuk booking ini.', 'Tersedia', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-25 05:48:34', '2026-06-25 05:48:34'),
(7, 37, 36, NULL, 199, 'Ojek Trip Jomblang Only', 'none', 128, 1, 1, 'Kerjakan add-on Ojek Trip Jomblang Only untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-25 05:57:14', '2026-07-03 15:22:53'),
(8, 36, 33, NULL, 177, 'Camera Insta360', 'drive_link', 108, 1, 1, 'Kerjakan add-on Camera Insta360 untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-25 05:57:44', '2026-07-02 04:26:08'),
(9, 36, 33, NULL, 179, 'Ojek Trip Goa Ngeleng Only', 'none', NULL, 1, 1, 'Kerjakan add-on Ojek Trip Goa Ngeleng Only untuk booking ini.', 'Tersedia', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-25 05:57:44', '2026-06-25 05:57:44'),
(10, 42, 36, NULL, 194, 'Dokumentasi Foto Camera + Video iPhone', 'drive_link', 108, 1, 1, 'Kerjakan add-on Dokumentasi Foto Camera + Video iPhone untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-26 11:58:41', '2026-07-05 07:45:43'),
(11, 42, 36, NULL, 198, 'Transportasi A - Mobil maksimal 5 orang', 'none', NULL, 1, 1, 'Kerjakan add-on Transportasi A - Mobil maksimal 5 orang untuk booking ini.', 'Tersedia', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-26 11:58:41', '2026-06-26 11:58:41'),
(12, 41, 36, NULL, 194, 'Dokumentasi Foto Camera + Video iPhone', 'drive_link', 128, 1, 1, 'Kerjakan add-on Dokumentasi Foto Camera + Video iPhone untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, 'in_progress', NULL, NULL, '2026-06-26 11:59:19', '2026-07-04 00:54:34'),
(13, 41, 36, NULL, 198, 'Transportasi A - Mobil maksimal 5 orang', 'none', 128, 1, 1, 'Kerjakan add-on Transportasi A - Mobil maksimal 5 orang untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-26 11:59:19', '2026-06-29 02:02:38'),
(14, 49, 33, NULL, 179, 'Ojek Trip Goa Ngeleng Only', 'none', 61, 1, 1, 'Kerjakan add-on Ojek Trip Goa Ngeleng Only untuk booking ini.', 'Selesai', NULL, NULL, NULL, NULL, 1, 'completed', '2026-07-05 23:05:45', 'Muhammad An Nizar', '2026-06-28 05:34:02', '2026-07-05 23:05:46'),
(15, 48, 33, NULL, 178, 'Baterai Drone 20 menit', 'none', 64, 1, 1, 'Kerjakan add-on Baterai Drone 20 menit untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-28 05:34:32', '2026-07-02 03:26:59'),
(16, 48, 33, NULL, 179, 'Ojek Trip Goa Ngeleng Only', 'none', 61, 1, 1, 'Kerjakan add-on Ojek Trip Goa Ngeleng Only untuk booking ini.', 'Selesai', NULL, NULL, NULL, NULL, 1, 'completed', '2026-07-05 23:05:52', 'Muhammad An Nizar', '2026-06-28 05:34:32', '2026-07-05 23:05:53'),
(17, 46, 33, NULL, 178, 'Baterai Drone 20 menit', 'drive_link', 64, 1, 1, 'Kerjakan add-on Baterai Drone 20 menit untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-28 05:36:34', '2026-07-02 04:26:31'),
(18, 53, 36, NULL, 194, 'Dokumentasi Foto Camera + Video iPhone', 'drive_link', 108, 1, 1, 'Kerjakan add-on Dokumentasi Foto Camera + Video iPhone untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-30 01:58:38', '2026-07-01 05:33:09'),
(19, 53, 36, NULL, 198, 'Transportasi A - Mobil maksimal 5 orang', 'none', 108, 1, 1, 'Kerjakan add-on Transportasi A - Mobil maksimal 5 orang untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-30 01:58:38', '2026-07-01 05:33:16'),
(20, 55, 33, NULL, 179, 'Ojek Trip Goa Ngeleng Only', 'none', NULL, 1, 1, 'Kerjakan add-on Ojek Trip Goa Ngeleng Only untuk booking ini.', 'Tersedia', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-30 08:38:49', '2026-07-04 00:48:05'),
(21, 57, 33, NULL, 177, 'Camera Insta360', 'drive_link', NULL, 1, 1, 'Kerjakan add-on Camera Insta360 untuk booking ini.', 'Tersedia', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-30 13:16:32', '2026-06-30 13:16:32'),
(22, 57, 33, NULL, 178, 'Baterai Drone 20 menit', 'drive_link', 64, 1, 1, 'Kerjakan add-on Baterai Drone 20 menit untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-30 13:16:32', '2026-07-02 03:27:31'),
(23, 59, 42, NULL, 235, 'Dokumentasi Camera', 'drive_link', 128, 1, 1, 'Kerjakan add-on Dokumentasi Camera untuk booking ini.', 'Selesai', 'https://drive.google.com/drive/folders/1pF1KyLNzyB7iFum5nuO-LOf8jBRBCCaB?usp=drive_link', 'https://drive.google.com/drive/folders/1pF1KyLNzyB7iFum5nuO-LOf8jBRBCCaB?usp=drive_link', NULL, NULL, 1, 'completed', '2026-07-03 15:20:24', 'Dimas Ananta Kusuma', '2026-07-01 08:32:26', '2026-07-03 15:20:25'),
(24, 62, 36, NULL, 199, 'Ojek Trip Jomblang Only', 'none', 61, 1, 1, 'Kerjakan add-on Ojek Trip Jomblang Only untuk booking ini.', 'Selesai', NULL, NULL, NULL, NULL, 1, 'completed', '2026-07-06 00:34:18', 'Muhammad An Nizar', '2026-07-02 03:14:41', '2026-07-06 00:34:19'),
(25, 66, 36, NULL, 199, 'Ojek Trip Jomblang Only', 'none', 64, 1, 1, 'Kerjakan add-on Ojek Trip Jomblang Only untuk booking ini.', 'Selesai', NULL, NULL, NULL, NULL, 1, 'completed', '2026-07-05 00:45:36', 'Muhammad Fauzi', '2026-07-03 04:13:33', '2026-07-05 00:45:36'),
(26, 70, 36, NULL, 197, 'Camera Insta360', 'none', 108, 1, 1, 'Kerjakan add-on Camera Insta360 untuk booking ini.', 'Diambil', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-07-05 05:40:34', '2026-07-05 05:41:03');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `addons`
--
ALTER TABLE `addons`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `idx_bookings_active_retention` (`archived_at`,`visible_until`),
  ADD KEY `idx_bookings_reminder_date` (`selected_date`,`status`),
  ADD KEY `idx_bookings_payment_status` (`payment_status`),
  ADD KEY `idx_bookings_selected_package` (`selected_package_id`),
  ADD KEY `idx_bookings_user_status` (`user_id`,`status`,`id`),
  ADD KEY `idx_bookings_trip_status_date` (`trip_id`,`status`,`selected_date`);

--
-- Indeks untuk tabel `booking_addons`
--
ALTER TABLE `booking_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `addon_id` (`addon_id`),
  ADD KEY `idx_booking_addons_trip_addon` (`trip_addon_id`);

--
-- Indeks untuk tabel `booking_participants`
--
ALTER TABLE `booking_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email_verification_token_hash` (`token_hash`),
  ADD KEY `idx_email_verification_user` (`user_id`,`used_at`,`expired_at`);

--
-- Indeks untuk tabel `package_price_tiers`
--
ALTER TABLE `package_price_tiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_package_price_tier` (`package_id`,`pax_count`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `pending_customer_registrations`
--
ALTER TABLE `pending_customer_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pending_customer_email` (`email`),
  ADD KEY `idx_pending_customer_expiry` (`expired_at`);

--
-- Indeks untuk tabel `private_price_tiers`
--
ALTER TABLE `private_price_tiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_trip_pax` (`trip_id`,`pax_count`);

--
-- Indeks untuk tabel `private_trip_packages`
--
ALTER TABLE `private_trip_packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_private_package_code` (`trip_id`,`package_code`),
  ADD KEY `idx_private_package_status` (`trip_id`,`status`,`sort_order`);

--
-- Indeks untuk tabel `reminder_logs`
--
ALTER TABLE `reminder_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_reminder_booking_type` (`booking_id`,`reminder_type`),
  ADD KEY `idx_reminder_status` (`status`,`updated_at`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_review_booking` (`booking_id`),
  ADD KEY `idx_reviews_public` (`status`,`created_at`),
  ADD KEY `idx_reviews_user` (`user_id`,`created_at`),
  ADD KEY `fk_reviews_trip` (`trip_id`);

--
-- Indeks untuk tabel `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trips_catalog` (`status`,`trip_type`,`id`);

--
-- Indeks untuk tabel `trip_addons`
--
ALTER TABLE `trip_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trip_addons_trip` (`trip_id`);

--
-- Indeks untuk tabel `trip_documentation_links`
--
ALTER TABLE `trip_documentation_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_trip_doc_open_schedule` (`schedule_id`),
  ADD UNIQUE KEY `uniq_trip_doc_private_date` (`trip_id`,`session_id`,`schedule_date`),
  ADD KEY `idx_trip_doc_trip` (`trip_id`),
  ADD KEY `fk_trip_doc_session` (`session_id`);

--
-- Indeks untuk tabel `trip_images`
--
ALTER TABLE `trip_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_id` (`trip_id`);

--
-- Indeks untuk tabel `trip_schedules`
--
ALTER TABLE `trip_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trip_schedules_active_retention` (`archived_at`,`visible_until`),
  ADD KEY `idx_trip_schedules_lookup` (`trip_id`,`status`,`schedule_date`);

--
-- Indeks untuk tabel `trip_sessions`
--
ALTER TABLE `trip_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_id` (`trip_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email_verified` (`email`,`email_verified`);

--
-- Indeks untuk tabel `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_sessions_token_hash` (`token_hash`),
  ADD KEY `idx_user_sessions_user_expires` (`user_id`,`expires_at`);

--
-- Indeks untuk tabel `worker_tasks`
--
ALTER TABLE `worker_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `addon_id` (`addon_id`),
  ADD KEY `idx_worker_tasks_trip_addon` (`trip_addon_id`),
  ADD KEY `idx_worker_tasks_worker_status` (`worker_id`,`status`,`id`),
  ADD KEY `idx_worker_tasks_booking_status` (`booking_id`,`status`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT untuk tabel `booking_addons`
--
ALTER TABLE `booking_addons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `booking_participants`
--
ALTER TABLE `booking_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT untuk tabel `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `package_price_tiers`
--
ALTER TABLE `package_price_tiers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `pending_customer_registrations`
--
ALTER TABLE `pending_customer_registrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT untuk tabel `private_price_tiers`
--
ALTER TABLE `private_price_tiers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT untuk tabel `private_trip_packages`
--
ALTER TABLE `private_trip_packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `reminder_logs`
--
ALTER TABLE `reminder_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `trips`
--
ALTER TABLE `trips`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT untuk tabel `trip_addons`
--
ALTER TABLE `trip_addons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT untuk tabel `trip_documentation_links`
--
ALTER TABLE `trip_documentation_links`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `trip_images`
--
ALTER TABLE `trip_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=831;

--
-- AUTO_INCREMENT untuk tabel `trip_schedules`
--
ALTER TABLE `trip_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT untuk tabel `trip_sessions`
--
ALTER TABLE `trip_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT untuk tabel `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT untuk tabel `worker_tasks`
--
ALTER TABLE `worker_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`schedule_id`) REFERENCES `trip_schedules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_4` FOREIGN KEY (`session_id`) REFERENCES `trip_sessions` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `booking_addons`
--
ALTER TABLE `booking_addons`
  ADD CONSTRAINT `booking_addons_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_addons_ibfk_2` FOREIGN KEY (`addon_id`) REFERENCES `addons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_booking_addons_trip_addon` FOREIGN KEY (`trip_addon_id`) REFERENCES `trip_addons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `booking_participants`
--
ALTER TABLE `booking_participants`
  ADD CONSTRAINT `booking_participants_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD CONSTRAINT `fk_email_verification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `package_price_tiers`
--
ALTER TABLE `package_price_tiers`
  ADD CONSTRAINT `fk_package_price_tiers_package` FOREIGN KEY (`package_id`) REFERENCES `private_trip_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `private_price_tiers`
--
ALTER TABLE `private_price_tiers`
  ADD CONSTRAINT `private_price_tiers_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `private_trip_packages`
--
ALTER TABLE `private_trip_packages`
  ADD CONSTRAINT `fk_private_packages_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reminder_logs`
--
ALTER TABLE `reminder_logs`
  ADD CONSTRAINT `fk_reminder_logs_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `trip_addons`
--
ALTER TABLE `trip_addons`
  ADD CONSTRAINT `fk_trip_addons_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `trip_documentation_links`
--
ALTER TABLE `trip_documentation_links`
  ADD CONSTRAINT `fk_trip_doc_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `trip_schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_trip_doc_session` FOREIGN KEY (`session_id`) REFERENCES `trip_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_trip_doc_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `trip_images`
--
ALTER TABLE `trip_images`
  ADD CONSTRAINT `trip_images_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `trip_schedules`
--
ALTER TABLE `trip_schedules`
  ADD CONSTRAINT `trip_schedules_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `trip_sessions`
--
ALTER TABLE `trip_sessions`
  ADD CONSTRAINT `trip_sessions_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `worker_tasks`
--
ALTER TABLE `worker_tasks`
  ADD CONSTRAINT `fk_worker_tasks_trip_addon` FOREIGN KEY (`trip_addon_id`) REFERENCES `trip_addons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `worker_tasks_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `worker_tasks_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `worker_tasks_ibfk_3` FOREIGN KEY (`addon_id`) REFERENCES `addons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `worker_tasks_ibfk_4` FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
