-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 04 Jun 2025 pada 15.14
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
-- Database: `sibk`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `chat`
--

CREATE TABLE `chat` (
  `id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `pengirim` enum('guru','siswa') NOT NULL,
  `pesan` text NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `chat`
--

INSERT INTO `chat` (`id`, `guru_id`, `siswa_id`, `pengirim`, `pesan`, `waktu`) VALUES
(1, 4, 1, 'guru', 'woi', '2025-06-04 11:45:09'),
(2, 1, 1, 'siswa', 'lah', '2025-06-04 11:46:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_konseling`
--

CREATE TABLE `jadwal_konseling` (
  `id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `status` enum('open','booked','done') NOT NULL DEFAULT 'open',
  `siswa_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `jadwal_konseling`
--

INSERT INTO `jadwal_konseling` (`id`, `guru_id`, `tanggal`, `jam`, `status`, `siswa_id`, `created_at`) VALUES
(1, 4, '2025-06-04', '18:50:00', 'done', 1, '2025-06-04 11:44:50'),
(4, 4, '2025-06-06', '22:10:00', 'open', NULL, '2025-06-04 12:12:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `konseling`
--

CREATE TABLE `konseling` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `catatan_sesi` text DEFAULT NULL,
  `tujuan` text DEFAULT NULL,
  `rencana_tindakan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `konseling`
--

INSERT INTO `konseling` (`id`, `siswa_id`, `guru_id`, `tanggal`, `jam`, `catatan_sesi`, `tujuan`, `rencana_tindakan`, `created_at`) VALUES
(1, 1, 4, '2025-06-04', '18:50:00', 'Siswa bermasalah dengan saya', 'memperbaiki akhlak', 'hukuman cambul', '2025-06-04 11:48:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggaran`
--

CREATE TABLE `pelanggaran` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa_profile`
--

CREATE TABLE `siswa_profile` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `siswa_profile`
--

INSERT INTO `siswa_profile` (`id`, `user_id`, `nis`, `kelas`, `alamat`, `telepon`) VALUES
(1, 2, '123456', '10-A', 'Jl. Melati No. 10', '081234567890'),
(2, 3, '234567', '11-B', 'Jl. Mawar No. 20', '082345678901');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(225) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('guru','siswa') NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `nama_lengkap`, `created_at`) VALUES
(1, 'guru_bk', '', '$2a$12$z.ETOVgJWR54WHbeymfbCeNRgnfQuhLyO5FTmfPD2oCKCXMeQAqrG', 'guru', 'Pak Budi', '2025-06-04 11:10:14'),
(2, 'siswa_andi', '', '$2a$12$z.ETOVgJWR54WHbeymfbCeNRgnfQuhLyO5FTmfPD2oCKCXMeQAqrG', 'siswa', 'Andi Susanto', '2025-06-04 11:10:14'),
(3, 'siswa_budi', '', '$2a$12$z.ETOVgJWR54WHbeymfbCeNRgnfQuhLyO5FTmfPD2oCKCXMeQAqrG', 'siswa', 'Budi Santoso', '2025-06-04 11:10:14'),
(4, 'lailatul', '', '$2a$12$GejsA91hrrBsuGahRKJgO.IrP2GAMBqGsnDI5SjbkRE9tuG6Zt65O', 'guru', 'Lailatul Jannah', '2025-06-04 11:35:35');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indeks untuk tabel `jadwal_konseling`
--
ALTER TABLE `jadwal_konseling`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indeks untuk tabel `konseling`
--
ALTER TABLE `konseling`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indeks untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indeks untuk tabel `siswa_profile`
--
ALTER TABLE `siswa_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `jadwal_konseling`
--
ALTER TABLE `jadwal_konseling`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `konseling`
--
ALTER TABLE `konseling`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `siswa_profile`
--
ALTER TABLE `siswa_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa_profile` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jadwal_konseling`
--
ALTER TABLE `jadwal_konseling`
  ADD CONSTRAINT `jadwal_konseling_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_konseling_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa_profile` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `konseling`
--
ALTER TABLE `konseling`
  ADD CONSTRAINT `konseling_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa_profile` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `konseling_ibfk_2` FOREIGN KEY (`guru_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  ADD CONSTRAINT `pelanggaran_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa_profile` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `siswa_profile`
--
ALTER TABLE `siswa_profile`
  ADD CONSTRAINT `siswa_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
