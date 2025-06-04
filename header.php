<?php
// header.php
require_once 'config.php';

// Tentukan halaman aktif agar navbar menyorot link yang sesuai
function navActive($page) {
    if (basename($_SERVER['PHP_SELF']) === $page) {
        return 'active';
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>SiBK - Sistem Informasi Bimbingan Konseling</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">SiBK</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <?php if (isGuru()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= navActive('dashboard_guru.php') ?>" href="dashboard_guru.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= navActive('jadwal_guru.php') ?>" href="jadwal_guru.php">Jadwal Konseling</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= navActive('layanan_konseling.php') ?>" href="layanan_konseling.php">Layanan Konseling</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= navActive('laporan.php') ?>" href="laporan.php">Laporan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= navActive('chat.php') ?>" href="chat.php">Chat</a>
                        </li>
                    <?php elseif (isSiswa()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= navActive('dashboard_siswa.php') ?>" href="dashboard_siswa.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= navActive('profile_siswa.php') ?>" href="profile_siswa.php">Profil Saya</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= navActive('jadwal_siswa.php') ?>" href="jadwal_siswa.php">Jadwal Konseling</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= navActive('riwayat_konseling.php') ?>" href="riwayat_konseling.php">Riwayat Konseling</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= navActive('chat.php') ?>" href="chat.php">Chat</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= navActive('login.php') ?>" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
