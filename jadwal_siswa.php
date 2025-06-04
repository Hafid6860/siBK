<?php
// jadwal_siswa.php
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

$notice = '';
// Proses booking slot
if (isset($_GET['book']) && is_numeric($_GET['book'])) {
    $jadwal_id = intval($_GET['book']);
    // Cek apakah slot masih open
    $stmtCheck = $pdo->prepare('SELECT status FROM jadwal_konseling WHERE id = ?');
    $stmtCheck->execute([$jadwal_id]);
    $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['status'] === 'open') {
        $stmtBook = $pdo->prepare('UPDATE jadwal_konseling SET status = "booked", siswa_id = ? WHERE id = ?');
        $stmtBook->execute([$siswa_id, $jadwal_id]);
        $notice = 'Berhasil memesan slot konseling.';
    } else {
        $notice = 'Slot sudah tidak tersedia.';
    }
}

// Ambil semua slot open (yang belum dipesan)
$stmt = $pdo->prepare('
    SELECT jk.id, jk.tanggal, jk.jam, u.nama_lengkap AS guru_nama
    FROM jadwal_konseling jk
    JOIN users u ON jk.guru_id = u.id
    WHERE jk.status = "open"
    ORDER BY jk.tanggal ASC, jk.jam ASC
');
$stmt->execute();
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil slot yang sudah dipesan oleh siswa ini
$stmt2 = $pdo->prepare('
    SELECT jk.id, jk.tanggal, jk.jam, u.nama_lengkap AS guru_nama
    FROM jadwal_konseling jk
    JOIN users u ON jk.guru_id = u.id
    WHERE jk.siswa_id = ? AND jk.status = "booked"
    ORDER BY jk.tanggal ASC, jk.jam ASC
');
$stmt2->execute([$siswa_id]);
$booked = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>
<h3>Jadwal Konseling</h3>

<?php if ($notice): ?>
    <div class="alert alert-info"><?= htmlspecialchars($notice) ?></div>
<?php endif; ?>

<!-- Daftar Slot Terbuka -->
<div class="card mb-4">
    <div class="card-header">Slot Konseling Terbuka</div>
    <div class="card-body">
        <?php if (count($slots) === 0): ?>
            <p>Tidak ada slot terbuka saat ini.</p>
        <?php else: ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Guru BK</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($slots as $s): ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($s['tanggal'])) ?></td>
                        <td><?= substr($s['jam'], 0, 5) ?></td>
                        <td><?= htmlspecialchars($s['guru_nama']) ?></td>
                        <td>
                            <a href="jadwal_siswa.php?book=<?= $s['id'] ?>" class="btn btn-sm btn-primary" onclick="return confirm('Pesan slot konseling ini?');">Pesan</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Slot yang Sudah Dipesan -->
<div class="card mb-4">
    <div class="card-header">Slot yang Sudah Dipesan</div>
    <div class="card-body">
        <?php if (count($booked) === 0): ?>
            <p>Anda belum memesan slot.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Guru BK</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($booked as $b): ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($b['tanggal'])) ?></td>
                        <td><?= substr($b['jam'], 0, 5) ?></td>
                        <td><?= htmlspecialchars($b['guru_nama']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
