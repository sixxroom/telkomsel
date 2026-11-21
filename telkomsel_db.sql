-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 21 Nov 2026 pada 10.31
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `telkomsel_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `countries`
--

INSERT INTO `countries` (`id`, `name`) VALUES
(1, 'Malaysia'),
(2, 'Singapura'),
(3, 'Thailand'),
(4, 'Jepang'),
(5, 'Korea Selatan'),
(6, 'Arab Saudi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `emergency_packages`
--

CREATE TABLE `emergency_packages` (
  `id` int(11) NOT NULL,
  `type` enum('darurat','pinjam','runmap') NOT NULL,
  `name` varchar(100) NOT NULL,
  `info` varchar(255) DEFAULT NULL,
  `quota_gb` decimal(6,2) NOT NULL DEFAULT 0.00,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `emergency_packages`
--

INSERT INTO `emergency_packages` (`id`, `type`, `name`, `info`, `quota_gb`, `price`) VALUES
(1, 'darurat', 'Paket Darurat 1', '1 GB / 1 hari', 1.00, 3000),
(2, 'darurat', 'Paket Darurat 2', '3 GB / 3 hari', 3.00, 8000),
(3, 'darurat', 'Paket Darurat 3', '5 GB / 7 hari', 5.00, 12000),
(4, 'pinjam', 'Pinjam Paket 1', '2 GB, bayar nanti', 2.00, 5000),
(5, 'pinjam', 'Pinjam Paket 2', '5 GB, bayar nanti', 5.00, 10000),
(6, 'pinjam', 'Pinjam Paket 3', '10 GB, bayar nanti', 10.00, 18000),
(7, 'runmap', 'RunMap Paket 1', 'Khusus peta & navigasi 1 GB', 1.00, 4000),
(8, 'runmap', 'RunMap Paket 2', 'Khusus peta & navigasi 3 GB', 3.00, 9000),
(9, 'runmap', 'RunMap Paket 3', 'Khusus peta & navigasi 5 GB', 5.00, 14000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `indihome_accounts`
--

CREATE TABLE `indihome_accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `network_id` varchar(30) NOT NULL,
  `speed_mbps` int(11) NOT NULL,
  `usage_gb` decimal(8,1) DEFAULT 0.0,
  `plan_name` varchar(100) DEFAULT NULL,
  `monthly_fee` int(11) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `indihome_accounts`
--

INSERT INTO `indihome_accounts` (`id`, `user_id`, `network_id`, `speed_mbps`, `usage_gb`, `plan_name`, `monthly_fee`, `status`) VALUES
(1, 1, 'IND-100234', 50, 320.5, 'Paket 50 Mbps', 375000, 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `other_menus`
--

CREATE TABLE `other_menus` (
  `id` int(11) NOT NULL,
  `label` varchar(50) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `other_menus`
--

INSERT INTO `other_menus` (`id`, `label`, `icon`, `is_active`) VALUES
(1, 'Games', 'games.png', 0),
(2, 'Musik', 'music.png', 0),
(3, 'Video', 'video.png', 0),
(4, 'Belanja', 'shop.png', 0),
(5, 'Asuransi', 'insurance.png', 0),
(6, 'Bantuan', 'help.png', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quota_gb` decimal(6,1) NOT NULL,
  `valid_days` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `packages`
--

INSERT INTO `packages` (`id`, `name`, `quota_gb`, `valid_days`, `price`, `is_active`) VALUES
(1, 'Internet Hemat', 3.0, 7, 15000, 1),
(2, 'Internet Lite', 8.0, 14, 35000, 1),
(3, 'Internet Plus', 15.0, 30, 60000, 1),
(4, 'Internet Max', 30.0, 30, 100000, 1),
(5, 'Internet Super', 60.0, 30, 150000, 1),
(6, 'Internet Unlimited', 100.0, 30, 200000, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `group_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `code`, `name`, `group_name`) VALUES
(1, 'ABC', 'ABC', 'mbanking'),
(2, 'MANJA', 'MANJA', 'mbanking'),
(3, 'RBI', 'RBI', 'bank'),
(4, 'BANKTINY', 'BANK TINY', 'bank'),
(5, 'MBANKING', 'M-Banking', 'pulsa'),
(6, 'INDOMI', 'Indomi', 'pulsa');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pulsa_options`
--

CREATE TABLE `pulsa_options` (
  `id` int(11) NOT NULL,
  `nominal` int(11) NOT NULL,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pulsa_options`
--

INSERT INTO `pulsa_options` (`id`, `nominal`, `price`) VALUES
(1, 5000, 6500),
(2, 10000, 11500),
(3, 20000, 21500),
(4, 50000, 51500),
(5, 100000, 101500),
(6, 200000, 201500);

-- --------------------------------------------------------

--
-- Struktur dari tabel `rewards`
--

CREATE TABLE `rewards` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `points_cost` int(11) NOT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `rewards`
--

INSERT INTO `rewards` (`id`, `name`, `points_cost`, `stock`) VALUES
(1, 'Voucher Pulsa 10rb', 200, 99),
(2, 'Voucher Kopi', 500, 50),
(3, 'Bonus Kuota 5GB', 800, 75),
(4, 'Voucher Belanja 50rb', 1500, 20),
(5, 'Headset Bluetooth', 3000, 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `roaming_packages`
--

CREATE TABLE `roaming_packages` (
  `id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `valid_days` int(11) NOT NULL,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roaming_packages`
--

INSERT INTO `roaming_packages` (`id`, `country_id`, `name`, `valid_days`, `price`) VALUES
(1, 1, 'Roaming Malaysia 5 Hari', 5, 99000),
(2, 2, 'Roaming Singapura 5 Hari', 5, 109000),
(3, 3, 'Roaming Thailand 7 Hari', 7, 129000),
(4, 4, 'Roaming Jepang 7 Hari', 7, 199000),
(5, 5, 'Roaming Korea 7 Hari', 7, 189000),
(6, 6, 'Roaming Saudi 10 Hari', 10, 249000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_type` enum('paket','pulsa','darurat','roaming','hadiah') NOT NULL,
  `product_id` int(11) NOT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `total` int(11) NOT NULL,
  `status` enum('pending','sukses','gagal') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `product_type`, `product_id`, `payment_method_id`, `total`, `status`, `created_at`) VALUES
(1, 1, 'pulsa', 6, 6, 201500, 'sukses', '2026-11-21 04:25:13'),
(2, 1, 'pulsa', 6, 6, 201500, 'sukses', '2026-11-21 04:25:15'),
(3, 1, 'paket', 6, NULL, 200000, 'sukses', '2026-11-21 04:25:26'),
(4, 1, 'darurat', 1, NULL, 3000, 'sukses', '2026-11-21 04:26:35'),
(5, 1, 'darurat', 9, NULL, 14000, 'sukses', '2026-11-21 04:45:42'),
(6, 1, 'paket', 2, 1, 35000, 'sukses', '2026-11-21 04:46:35'),
(7, 1, 'pulsa', 3, 6, 21500, 'sukses', '2026-11-21 04:46:42'),
(8, 1, 'pulsa', 6, 1, 201500, 'sukses', '2026-11-21 04:46:48'),
(9, 2, 'hadiah', 1, NULL, 200, 'sukses', '2026-11-21 04:51:46'),
(10, 3, 'darurat', 4, NULL, 5000, 'sukses', '2026-11-21 05:06:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `name` varchar(100) NOT NULL,
  `card_type` enum('halo','simpati') NOT NULL,
  `pulsa_balance` int(11) NOT NULL DEFAULT 0,
  `quota_gb` decimal(8,2) NOT NULL DEFAULT 0.00,
  `emergency_quota_gb` decimal(8,2) NOT NULL DEFAULT 0.00,
  `points` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `phone_number`, `name`, `card_type`, `pulsa_balance`, `quota_gb`, `emergency_quota_gb`, `points`, `created_at`) VALUES
(1, '081234567890', 'Budi Santoso', 'halo', 453000, 20.50, 6.00, 1200, '2026-11-21 03:51:02'),
(2, '085211112222', 'Siti Aminah', 'simpati', 15000, 4.00, 0.50, 150, '2026-11-21 03:51:02'),
(3, '067676767676', 'Yadss', 'simpati', 11995000, 999999.99, 999999.99, 10002, '2026-11-21 05:03:00');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `emergency_packages`
--
ALTER TABLE `emergency_packages`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `indihome_accounts`
--
ALTER TABLE `indihome_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `other_menus`
--
ALTER TABLE `other_menus`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `pulsa_options`
--
ALTER TABLE `pulsa_options`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `roaming_packages`
--
ALTER TABLE `roaming_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `emergency_packages`
--
ALTER TABLE `emergency_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `indihome_accounts`
--
ALTER TABLE `indihome_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `other_menus`
--
ALTER TABLE `other_menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `pulsa_options`
--
ALTER TABLE `pulsa_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `roaming_packages`
--
ALTER TABLE `roaming_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `indihome_accounts`
--
ALTER TABLE `indihome_accounts`
  ADD CONSTRAINT `indihome_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `roaming_packages`
--
ALTER TABLE `roaming_packages`
  ADD CONSTRAINT `roaming_packages_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
