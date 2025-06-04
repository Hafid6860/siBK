<?php
// dashboard_siswa.php
require_once 'config.php';
if (!isLoggedIn() || !isSiswa()) {
    header('Location: login.php');
    exit;
}

// Ambil data siswa_profile
$stmt0 = $pdo->prepare('SELECT * FROM siswa_profile WHERE user_id = ?');
$stmt0->execute([$_SESSION['user_id']]);
$profile = $stmt0->fetch(PDO::FETCH_ASSOC);

// Jadwal konseling terdekat yang sudah dipesan (status = booked)
$stmt1 = $pdo->prepare('
    SELECT jk.id, jk.tanggal, jk.jam, u.nama_lengkap AS guru_nama
    FROM jadwal_konseling jk
    JOIN users u ON jk.guru_id = u.id
    WHERE jk.siswa_id = ? AND jk.status = "booked"
    ORDER BY jk.tanggal ASC, jk.jam ASC
    LIMIT 5
');
$stmt1->execute([$profile['id']]);
$jadwal_dekat = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// Riwayat konseling terakhir (3 kali terakhir)
$stmt2 = $pdo->prepare('
    SELECT k.tanggal, k.jam, u.nama_lengkap AS guru_nama
    FROM konseling k
    JOIN users u ON k.guru_id = u.id
    WHERE k.siswa_id = ?
    ORDER BY k.tanggal DESC, k.jam DESC
    LIMIT 3
');
$stmt2->execute([$profile['id']]);
$riwayat_terakhir = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>
<h3>Dashboard Siswa</h3>

<div class="row">
    <!-- Profil Singkat -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">Profil Singkat</div>
            <div class="card-body">
                <p><strong>Nama:</strong> <?= htmlspecialchars($_SESSION['nama_lengkap']) ?></p>
                <p><strong>NIS:</strong> <?= htmlspecialchars($profile['nis']) ?></p>
                <p><strong>Kelas:</strong> <?= htmlspecialchars($profile['kelas']) ?></p>
                <a href="profile_siswa.php" class="btn btn-sm btn-primary">Lihat Detail Profil</a>
            </div>
        </div>
    </div>

    <!-- Jadwal Terdekat -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">Jadwal Konseling Terdekat</div>
            <div class="card-body">
                <?php if (count($jadwal_dekat) === 0): ?>
                    <p>Tidak ada jadwal terdekat.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($jadwal_dekat as $jd): ?>
                            <li class="list-group-item">
                                <?= date('d-m-Y', strtotime($jd['tanggal'])) ?>, <?= substr($jd['jam'], 0, 5) ?> 
                                dengan <?= htmlspecialchars($jd['guru_nama']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <a href="jadwal_siswa.php" class="btn btn-sm btn-secondary mt-2">Lihat Semua</a>
            </div>
        </div>
    </div>

    <!-- Riwayat Konseling Terakhir -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">Riwayat Konseling Terakhir</div>
            <div class="card-body">
                <?php if (count($riwayat_terakhir) === 0): ?>
                    <p>Belum ada riwayat konseling.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($riwayat_terakhir as $rt): ?>
                            <li class="list-group-item">
                                <?= date('d-m-Y', strtotime($rt['tanggal'])) ?>, <?= substr($rt['jam'], 0, 5) ?> 
                                dengan <?= htmlspecialchars($rt['guru_nama']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <a href="riwayat_konseling.php" class="btn btn-sm btn-secondary mt-2">Lihat Semua</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
