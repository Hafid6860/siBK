<?php
// config.php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');         // sesuaikan dengan password MySQL Anda
define('DB_NAME', 'sibk');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isGuru() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'guru');
}

function isSiswa() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'siswa');
}
?>
