<?php
// riwayat_konseling.php
require_once 'config.php';
if (!isLoggedIn() || !isSiswa()) {
    header('Location: login.php');
    exit;
}

// Ambil ID siswa_profile
$stmt0 = $pdo->prepare('SELECT id FROM siswa_profile WHERE user_id = ?');
$stmt0->execute([$_SESSION['user_id']]);
$profile = $stmt0->fetch(PDO::FETCH_ASSOC);
$siswa_id = $profile['id'];

// Ambil semua riwayat konseling
$stmt = $pdo->prepare('
    SELECT k.tanggal, k.jam, u.nama_lengkap AS guru_nama, k.catatan_sesi, k.tujuan, k.rencana_tindakan
    FROM konseling k
    JOIN users u ON k.guru_id = u.id
    WHERE k.siswa_id = ?
    ORDER BY k.tanggal DESC, k.jam DESC
');
$stmt->execute([$siswa_id]);
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>
<h3>Riwayat Konseling</h3>

<?php if (count($riwayat) === 0): ?>
    <p>Belum ada sesi konseling.</p>
<?php else: ?>
    <?php foreach ($riwayat as $rk): ?>
        <div class="card mb-3">
            <div class="card-header">
                <?= date('d-m-Y', strtotime($rk['tanggal'])) ?>, <?= substr($rk['jam'], 0, 5) ?> dengan <?= htmlspecialchars($rk['guru_nama']) ?>
            </div>
            <div class="card-body">
                <p><strong>Catatan Sesi:</strong><br><?= nl2br(htmlspecialchars($rk['catatan_sesi'])) ?></p>
                <p><strong>Tujuan:</strong><br><?= nl2br(htmlspecialchars($rk['tujuan'])) ?></p>
                <p><strong>Rencana Tindakan:</strong><br><?= nl2br(htmlspecialchars($rk['rencana_tindakan'])) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>
