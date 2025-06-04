<?php
// layanan_konseling.php
require_once 'config.php';
if (!isLoggedIn() || !isGuru()) {
    header('Location: login.php');
    exit;
}

$notice = '';
$jadwal_id = isset($_GET['jadwal_id']) ? intval($_GET['jadwal_id']) : 0;

// Ambil data jadwal & siswa dari jadwal_id, termasuk nama siswa dari users
$stmt0 = $pdo->prepare('
    SELECT 
        jk.tanggal, 
        jk.jam, 
        sp.id AS siswa_id, 
        u.nama_lengkap AS siswa_nama
    FROM jadwal_konseling jk
    JOIN siswa_profile sp ON jk.siswa_id = sp.id
    JOIN users u ON sp.user_id = u.id
    WHERE jk.id = ? 
      AND jk.guru_id = ?
');
$stmt0->execute([$jadwal_id, $_SESSION['user_id']]);
$data = $stmt0->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    header('Location: dashboard_guru.php');
    exit;
}

// Proses simpan catatan sesi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_sesi'])) {
    $catatan_sesi = trim($_POST['catatan_sesi']);
    $tujuan = trim($_POST['tujuan']);
    $rencana = trim($_POST['rencana_tindakan']);

    if ($catatan_sesi === '' || $tujuan === '' || $rencana === '') {
        $notice = 'Harap isi semua kolom.';
    } else {
        // Simpan ke tabel konseling
        $stmt1 = $pdo->prepare('
            INSERT INTO konseling (siswa_id, guru_id, tanggal, jam, catatan_sesi, tujuan, rencana_tindakan)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt1->execute([
            $data['siswa_id'],
            $_SESSION['user_id'],
            $data['tanggal'],
            $data['jam'],
            $catatan_sesi,
            $tujuan,
            $rencana
        ]);

        // Update status jadwal menjadi 'done'
        $stmt2 = $pdo->prepare('UPDATE jadwal_konseling SET status = "done" WHERE id = ?');
        $stmt2->execute([$jadwal_id]);

        $notice = 'Catatan sesi berhasil disimpan.';
    }
}
?>

<?php include 'header.php'; ?>
<h3>Catat Sesi Konseling</h3>

<?php if ($notice): ?>
    <div class="alert alert-info"><?= htmlspecialchars($notice) ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        Jadwal: <?= date('d-m-Y', strtotime($data['tanggal'])) ?>, <?= substr($data['jam'], 0, 5) ?> 
        (Siswa: <?= htmlspecialchars($data['siswa_nama']) ?>)
    </div>
    <div class="card-body">
        <form method="POST" action="layanan_konseling.php?jadwal_id=<?= $jadwal_id ?>">
            <div class="mb-3">
                <label for="catatan_sesi" class="form-label">Catatan Sesi</label>
                <textarea class="form-control" id="catatan_sesi" name="catatan_sesi" rows="3" required><?= isset($_POST['catatan_sesi']) ? htmlspecialchars($_POST['catatan_sesi']) : '' ?></textarea>
            </div>
            <div class="mb-3">
                <label for="tujuan" class="form-label">Tujuan</label>
                <textarea class="form-control" id="tujuan" name="tujuan" rows="2" required><?= isset($_POST['tujuan']) ? htmlspecialchars($_POST['tujuan']) : '' ?></textarea>
            </div>
            <div class="mb-3">
                <label for="rencana_tindakan" class="form-label">Rencana Tindakan</label>
                <textarea class="form-control" id="rencana_tindakan" name="rencana_tindakan" rows="2" required><?= isset($_POST['rencana_tindakan']) ? htmlspecialchars($_POST['rencana_tindakan']) : '' ?></textarea>
            </div>
            <button type="submit" name="simpan_sesi" class="btn btn-primary">Simpan</button>
            <a href="dashboard_guru.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
