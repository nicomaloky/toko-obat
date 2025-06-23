-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2025 at 12:04 PM
-- Server version: 10.4.28-MariaDB-log
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `el_pharmacy`
--

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Obat Anak-anak'),
(2, 'Vitamin'),
(3, 'Obat Dewasa'),
(4, 'Produk Ibu Hamil & Menyusui'),
(5, 'Alat Kesehatan & Perawatan Luka'),
(6, 'Kesehatan Mental');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  `nama_penerima` varchar(255) NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `telepon_penerima` varchar(20) NOT NULL,
  `catatan` text DEFAULT NULL,
  `tanggal_pesan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `user_id`, `produk_id`, `jumlah`, `total_harga`, `status`, `nama_penerima`, `alamat_pengiriman`, `telepon_penerima`, `catatan`, `tanggal_pesan`) VALUES
(1, 1, 2, 1, 15000.00, 'cancelled', 'curut', 'kosan santika 8', '0871621923', 'kamu udah makan belum', '2025-06-15 07:14:22'),
(2, 1, 2, 1, 15000.00, 'completed', 'curut', 'kosan santika 999', '000', 'kam udah makan belum', '2025-06-15 07:15:31'),
(3, 1, 2, 1, 15000.00, 'completed', 'q312asdaasd', 'sad', '2342', 'w', '2025-06-15 07:17:24'),
(4, 1, 2, 1, 15000.00, 'completed', 'cukurukuk', 'aokwokaw', '088888888', 'hy', '2025-06-15 07:23:18'),
(5, 1, 6, 10, 50000.00, 'completed', '', '', '', '', '2025-06-23 04:57:20'),
(6, 1, 6, 10, 50000.00, 'cancelled', '', '', '', '', '2025-06-23 04:57:35'),
(7, 1, 12, 1, 12000.00, 'completed', '', '', '', '', '2025-06-23 06:57:29'),
(8, 1, 1, 1, 10000.00, 'pending', 'nico', 'dihatimu', '08888888', 'jhbjbj', '2025-06-23 09:14:33');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `nama_produk` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `gambar` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `id_kategori`, `nama_produk`, `deskripsi`, `harga`, `stok`, `gambar`) VALUES
(1, 3, 'Paracetamol', 'Obat penurun panas dan pereda nyeri.', 10000.00, 16, 'paracetamol.jpg'),
(2, 2, 'Vitamin C', 'Meningkatkan daya tahan tubuh.', 15000.00, 0, 'vitaminc.jpg'),
(3, 3, 'Amoxicillin', 'Antibiotik untuk infeksi bakteri.', 20000.00, 0, 'amoxicillin.jpg'),
(4, 3, 'Spasminal', 'Obat untuk meredakan nyeri menstruasi.', 12900.00, 90, 'spasminal.jpg'),
(5, 3, 'Promaag', 'Obat untuk masalah lambung dan asam lambung.', 11000.00, 0, 'Promag.jpg'),
(6, 3, 'Mefenamic Acid', 'Obat untuk meredakan nyeri.', 5000.00, 8, 'Mefenamic Acid.jpg'),
(7, 2, 'Caviplex', 'Obat Multivitamin.', 9000.00, 13, 'Caviplex.jpg'),
(8, 2, 'Sangobion', 'Obat untuk menambah darah.', 19000.00, 11, 'Sangobion.jpg'),
(9, 5, 'Salonpas', 'Plester untuk meredakan nyeri akibat otot tegang, otot tertarik, keseleo, cedera, atau radang sendi.', 7000.00, 20, 'salonpas.jpg'),
(10, 5, 'Betadine Antiseptic', 'Cairan untuk mencegah pertumbuhan dan membunuh kuman penyebab infeksi pada kulit, seperti infeksi akibat luka gores atau luka bakar ringan.', 33000.00, 17, 'Betadine Antiseptic.jpg'),
(11, 5, 'FreshCare Matcha', 'Produk inovatif yang menggabungkan double inhaler dan roll on dalam satu kemasan. Produk ini berbagai manfaat seperti untuk kerokan, pijat, dan relaksasi.', 14000.00, 12, 'FreshCare Matcha.jpg'),
(12, 5, 'Hansaplast', 'Plester untuk menutup luka.', 12000.00, 9, 'Hansaplast.jpg'),
(20, 6, 'nopi cantik', 'cantik banget', 99999999.00, 1, '1750662178_WhatsApp Image 2025-06-19 at 16.18.45_751e9248.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'user', '$2y$10$swhuFYIWb6oKulHuMnFSEuu9XRNoK2cWiS0LKTEaWESNQ0M.CWzdO', 'user', '2025-06-15 05:47:45'),
(2, 'admin', '$2y$10$lXtcoIiUTluc6F.WvUcAXeEg6r2QCwreAnKphPMfY//ouJY/3sBKS', 'admin', '2025-06-15 06:03:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
