<?php
// profile_siswa.php
require_once 'config.php';
if (!isLoggedIn() || !isSiswa()) {
    header('Location: login.php');
    exit;
}

// Ambil data siswa_profile
$stmt = $pdo->prepare('SELECT * FROM siswa_profile WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Riwayat konseling (semua)
$stmt2 = $pdo->prepare('
    SELECT k.tanggal, k.jam, u.nama_lengkap AS guru_nama, k.catatan_sesi, k.tujuan, k.rencana_tindakan
    FROM konseling k
    JOIN users u ON k.guru_id = u.id
    WHERE k.siswa_id = ?
    ORDER BY k.tanggal DESC, k.jam DESC
');
$stmt2->execute([$profile['id']]);
$riwayat_konseling = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Riwayat pelanggaran (jika ada)
$stmt3 = $pdo->prepare('
    SELECT tanggal, keterangan
    FROM pelanggaran
    WHERE siswa_id = ?
    ORDER BY tanggal DESC
');
$stmt3->execute([$profile['id']]);
$riwayat_pelanggaran = $stmt3->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>
<h3>Profil Siswa</h3>

<div class="row">
    <!-- Data Diri -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Data Diri Lengkap</div>
            <div class="card-body">
                <p><strong>Nama:</strong> <?= htmlspecialchars($_SESSION['nama_lengkap']) ?></p>
                <p><strong>NIS:</strong> <?= htmlspecialchars($profile['nis']) ?></p>
                <p><strong>Kelas:</strong> <?= htmlspecialchars($profile['kelas']) ?></p>
                <p><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($profile['alamat'])) ?></p>
                <p><strong>Telepon:</strong> <?= htmlspecialchars($profile['telepon']) ?></p>
            </div>
        </div>
    </div>

    <!-- Riwayat Pelanggaran -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Riwayat Pelanggaran</div>
            <div class="card-body">
                <?php if (count($riwayat_pelanggaran) === 0): ?>
                    <p>Tidak ada catatan pelanggaran.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($riwayat_pelanggaran as $pl): ?>
                            <li class="list-group-item">
                                <?= date('d-m-Y', strtotime($pl['tanggal'])) ?>: <?= htmlspecialchars($pl['keterangan']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Riwayat Konseling -->
<div class="card mb-4">
    <div class="card-header">Riwayat Konseling</div>
    <div class="card-body">
        <?php if (count($riwayat_konseling) === 0): ?>
            <p>Belum ada riwayat konseling.</p>
        <?php else: ?>
            <?php foreach ($riwayat_konseling as $rk): ?>
                <div class="mb-3">
                    <h6><?= date('d-m-Y', strtotime($rk['tanggal'])) ?>, <?= substr($rk['jam'], 0, 5) ?> dengan <?= htmlspecialchars($rk['guru_nama']) ?></h6>
                    <p><strong>Catatan Sesi:</strong><br><?= nl2br(htmlspecialchars($rk['catatan_sesi'])) ?></p>
                    <p><strong>Tujuan:</strong><br><?= nl2br(htmlspecialchars($rk['tujuan'])) ?></p>
                    <p><strong>Rencana Tindakan:</strong><br><?= nl2br(htmlspecialchars($rk['rencana_tindakan'])) ?></p>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
