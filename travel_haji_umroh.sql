-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 24, 2026 at 02:19 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `travel_haji_umroh`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `data_pembayaran`
-- (See below for the actual view)
--
CREATE TABLE `data_pembayaran` (
`nama_paket` varchar(100)
,`tanggal_bayar` date
,`nominal` decimal(12,2)
,`status` enum('Menunggu','Berangkat','Proses','Pulang','Selesai')
);

-- --------------------------------------------------------

--
-- Table structure for table `jamaah`
--

CREATE TABLE `jamaah` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `nik` varchar(16) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `alamat` text NOT NULL,
  `no_hp` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jamaah`
--

INSERT INTO `jamaah` (`id`, `user_id`, `nik`, `nama_lengkap`, `jenis_kelamin`, `alamat`, `no_hp`) VALUES
(21, 27, '9283253018273511', 'Yuda rahmanda', 'L', 'Enrekang, Sulawesi Selatan', '083170955012'),
(23, 29, '3012935102835127', 'Kamari imut', 'P', 'Batusangkar, Sumatra Barat', '083729172921'),
(25, 31, '320291028095192', 'Rasya gilang', 'L', 'Batusangkar', '083729172921'),
(26, 32, '3821927384023827', 'Rajian Santriazi', 'L', 'Kiaracondong, Bandung', '083012955012');

-- --------------------------------------------------------

--
-- Table structure for table `keberangkatan`
--

CREATE TABLE `keberangkatan` (
  `id` int NOT NULL,
  `paket_id` int NOT NULL,
  `tanggal_berangkat` date NOT NULL,
  `tgl_kepulangan` date DEFAULT NULL,
  `maskapai` varchar(100) DEFAULT NULL,
  `embarkasi` varchar(100) DEFAULT NULL,
  `kuota_penerbangan` int DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `keberangkatan`
--

INSERT INTO `keberangkatan` (`id`, `paket_id`, `tanggal_berangkat`, `tgl_kepulangan`, `maskapai`, `embarkasi`, `kuota_penerbangan`, `keterangan`) VALUES
(15, 6, '2026-11-25', '2027-09-24', 'Qatar Airways', 'Surabaya (SUB)', 40, 'Testing'),
(16, 5, '2026-10-23', '2026-10-30', 'Etihad Airways', 'Surabaya (SUB)', 50, '');

-- --------------------------------------------------------

--
-- Table structure for table `paket`
--

CREATE TABLE `paket` (
  `id` int NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `jenis` enum('Haji','Umroh') NOT NULL,
  `kuota` int NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `durasi` varchar(50) DEFAULT NULL,
  `deskripsi` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `paket`
--

INSERT INTO `paket` (`id`, `nama_paket`, `jenis`, `kuota`, `harga`, `durasi`, `deskripsi`) VALUES
(2, 'Haji VIP', 'Haji', 25, '125000000.00', '15', 'Hotel Premium, Transportasi Eksklusif, Konsumsi, Pembimbing'),
(4, 'Paket Umroh VIP / Premium', 'Umroh', 10, '79000000.00', '16', 'Fasilitas mewah, hotel bintang 5 yang berada dekat dengan Masjidil Haram atau Masjid Nabawi'),
(5, 'Haji Khusus VIP', 'Haji', 9, '69000000.00', '29', 'Program haji khusus dengan layanan akomodasi premium'),
(6, 'Umroh Plus Turki Cappadocia', 'Umroh', 9, '36000000.00', '17', 'Umroh Plus Turki Cappadocia → Umroh dan city tour Istanbul dan Cappadocia'),
(7, 'Haji Khusus', 'Haji', 3, '58600000.00', '26', 'Program haji khusus dengan layanan akomodasi dan pembimbing'),
(8, 'Umroh Plus Thaif', 'Umroh', 5, '79600000.00', '12', 'Umroh, City Tour Thaif, Hotel, Transportas');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int NOT NULL,
  `jamaah_id` int NOT NULL,
  `pendaftaran_id` int NOT NULL,
  `keberangkatan_id` int DEFAULT NULL,
  `tanggal_bayar` date NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `sisa_pembayaran` bigint NOT NULL,
  `status` enum('Pending','Valid','Ditolak') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `jamaah_id`, `pendaftaran_id`, `keberangkatan_id`, `tanggal_bayar`, `nominal`, `sisa_pembayaran`, `status`) VALUES
(36, 21, 44, NULL, '2026-09-24', '36000000.00', 36000000, 'Pending'),
(37, 26, 45, NULL, '2026-09-24', '69000000.00', 69000000, 'Valid'),
(38, 25, 46, NULL, '2026-09-24', '79000000.00', 79000000, 'Pending'),
(39, 23, 47, NULL, '2026-09-24', '125000000.00', 125000000, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id` int NOT NULL,
  `jamaah_id` int NOT NULL,
  `paket_id` int NOT NULL,
  `keberangkatan_id` int DEFAULT NULL,
  `tgl_daftar` date NOT NULL,
  `status` enum('Menunggu','Berangkat','Proses','Pulang','Selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id`, `jamaah_id`, `paket_id`, `keberangkatan_id`, `tgl_daftar`, `status`) VALUES
(44, 21, 6, 15, '2026-09-24', 'Berangkat'),
(45, 26, 5, 16, '2026-09-24', 'Berangkat'),
(46, 25, 4, NULL, '2026-09-24', 'Menunggu'),
(47, 23, 2, NULL, '2026-09-24', 'Menunggu');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas','jamaah') NOT NULL,
  `jamaah_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `role`, `jamaah_id`) VALUES
(1, 'Rinjani', 'rinjanicantik', 'admin', NULL),
(5, 'Zahra', '$2y$10$HzHGL4R/rZ5KAx7AaZSY7uTRW.oFcGC.V9CKlu0ivUtZeaFUZ4XCO', 'petugas', NULL),
(23, 'luthfi', '$2y$10$O1U6U1oMnl0avbf5YUimC.krl0zqMgbT9jp4DBBz5ntUesweasFLy', 'petugas', NULL),
(27, 'yuda aja', '$2y$10$73gXx3d12lG7o6RZTd1eUuOoY/Cr3IRrkeNIlRKkfAzYgtdrrBfxe', 'jamaah', 21),
(29, 'Kamari', '$2y$10$a3SmKwNbBUhtY9FrFEaw7./aGOMM1c4/JXDpyFF0AaQ37Q8CFzvNS', 'jamaah', 23),
(31, 'rasya', '$2y$10$znH7Lrhg1XpCvytLwnoX7uby9LwlUQpwRsZt7RZ/bQi5kFIIbSC2u', 'jamaah', 25),
(32, 'Rajian', '$2y$10$FS.JokNP35BPtDl.J0TUj.HcZilpon782koZZZptKJwFkSExsx/iu', 'jamaah', 26);

-- --------------------------------------------------------

--
-- Structure for view `data_pembayaran`
--
DROP TABLE IF EXISTS `data_pembayaran`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `data_pembayaran`  AS SELECT `paket`.`nama_paket` AS `nama_paket`, `pembayaran`.`tanggal_bayar` AS `tanggal_bayar`, `pembayaran`.`nominal` AS `nominal`, `pendaftaran`.`status` AS `status` FROM (((`pembayaran` join `jamaah` on((`pembayaran`.`jamaah_id` = `jamaah`.`id`))) join `pendaftaran` on((`jamaah`.`id` = `pendaftaran`.`jamaah_id`))) join `paket` on((`pendaftaran`.`paket_id` = `paket`.`id`)))  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jamaah`
--
ALTER TABLE `jamaah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `keberangkatan`
--
ALTER TABLE `keberangkatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paket_id` (`paket_id`);

--
-- Indexes for table `paket`
--
ALTER TABLE `paket`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jamaah_id` (`jamaah_id`),
  ADD KEY `keberangkatan_id` (`keberangkatan_id`),
  ADD KEY `pendaftaran_id` (`pendaftaran_id`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pendaftaran_jamaah` (`jamaah_id`),
  ADD KEY `fk_pendaftaran_paket` (`paket_id`),
  ADD KEY `fk_pendaftaran_keberangkatan` (`keberangkatan_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jamaah`
--
ALTER TABLE `jamaah`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `keberangkatan`
--
ALTER TABLE `keberangkatan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `paket`
--
ALTER TABLE `paket`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jamaah`
--
ALTER TABLE `jamaah`
  ADD CONSTRAINT `jamaah_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `keberangkatan`
--
ALTER TABLE `keberangkatan`
  ADD CONSTRAINT `keberangkatan_ibfk_1` FOREIGN KEY (`paket_id`) REFERENCES `paket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`jamaah_id`) REFERENCES `jamaah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`keberangkatan_id`) REFERENCES `keberangkatan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `fk_pendaftaran_jamaah` FOREIGN KEY (`jamaah_id`) REFERENCES `jamaah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pendaftaran_keberangkatan` FOREIGN KEY (`keberangkatan_id`) REFERENCES `keberangkatan` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pendaftaran_paket` FOREIGN KEY (`paket_id`) REFERENCES `paket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
