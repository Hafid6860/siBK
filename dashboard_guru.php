<?php
// dashboard_guru.php
require_once 'config.php';
if (!isLoggedIn() || !isGuru()) {
    header('Location: login.php');
    exit;
}

$today = date('Y-m-d');

// 1. Ringkasan jadwal konseling hari ini
//    – gunakan JOIN berjenjang: jadwal_konseling → siswa_profile (sp) → users (su)
$stmt = $pdo->prepare('
    SELECT 
        jk.id,
        jk.jam,
        CASE 
            WHEN jk.status = "open" THEN "Belum Dipesan"
            WHEN jk.status = "booked" THEN CONCAT("Dipesan oleh ", su.nama_lengkap)
            ELSE "Selesai"
        END AS status_slot
    FROM jadwal_konseling jk
    LEFT JOIN siswa_profile sp ON jk.siswa_id = sp.id
    LEFT JOIN users su ON sp.user_id = su.id
    WHERE jk.guru_id = ? 
      AND jk.tanggal = ?
    ORDER BY jk.jam ASC
');
$stmt->execute([$_SESSION['user_id'], $today]);
$jadwal_hari_ini = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Notifikasi: siswa yang baru mengajukan (status = 'booked' tapi belum ada catatan sesi di konseling)
$stmt2 = $pdo->prepare('
    SELECT 
        jk.id AS jadwal_id,
        su.nama_lengkap AS siswa_nama,
        jk.tanggal,
        jk.jam
    FROM jadwal_konseling jk
    JOIN siswa_profile sp ON jk.siswa_id = sp.id
    JOIN users su ON sp.user_id = su.id
    LEFT JOIN konseling k 
        ON k.siswa_id = sp.id 
       AND k.tanggal = jk.tanggal 
       AND k.jam = jk.jam
    WHERE jk.guru_id = ? 
      AND jk.status = "booked" 
      AND k.id IS NULL
    ORDER BY jk.tanggal DESC, jk.jam DESC
');
$stmt2->execute([$_SESSION['user_id']]);
$notif_konseling = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// 3. Statistik layanan konseling per bulan (6 bulan terakhir)
$stmt3 = $pdo->prepare('
    SELECT 
        DATE_FORMAT(k.tanggal, "%Y-%m") AS bulan, 
        COUNT(*) AS total_sesi
    FROM konseling k
    WHERE k.guru_id = ?
      AND k.tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(k.tanggal, "%Y-%m")
    ORDER BY bulan ASC
');
$stmt3->execute([$_SESSION['user_id']]);
$statistik = $stmt3->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>
<h3>Dashboard Guru BK</h3>

<!-- Ringkasan Jadwal Hari Ini -->
<div class="card mb-4">
    <div class="card-header">
        Jadwal Konseling Hari Ini (<?= date('d F Y', strtotime($today)) ?>)
    </div>
    <div class="card-body">
        <?php if (count($jadwal_hari_ini) === 0): ?>
            <p>Tidak ada jadwal hari ini.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Jam</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($jadwal_hari_ini as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars(substr($row['jam'], 0, 5)) ?></td>
                        <td><?= htmlspecialchars($row['status_slot']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Notifikasi Siswa Mengajukan -->
<div class="card mb-4">
    <div class="card-header">
        Notifikasi: Siswa Mengajukan Konseling
    </div>
    <div class="card-body">
        <?php if (count($notif_konseling) === 0): ?>
            <p>Tidak ada notifikasi baru.</p>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($notif_konseling as $n): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($n['siswa_nama']) ?></strong> 
                        mengajukan konseling tanggal 
                        <?= date('d-m-Y', strtotime($n['tanggal'])) ?> 
                        jam <?= substr($n['jam'], 0, 5) ?>.
                        <a href="layanan_konseling.php?jadwal_id=<?= $n['jadwal_id'] ?>" class="btn btn-sm btn-primary float-end">Catat Sesi</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<!-- Statistik Layanan Konseling Per Bulan -->
<div class="card mb-4">
    <div class="card-header">
        Statistik Layanan Konseling (6 Bulan Terakhir)
    </div>
    <div class="card-body">
        <?php if (count($statistik) === 0): ?>
            <p>Belum ada data konseling.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Total Sesi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($statistik as $s): ?>
                    <tr>
                        <td><?= date('F Y', strtotime($s['bulan'] . '-01')) ?></td>
                        <td><?= $s['total_sesi'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
