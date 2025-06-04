<?php
// laporan.php
require_once 'config.php';
if (!isLoggedIn() || !isGuru()) {
    header('Location: login.php');
    exit;
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'harian';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$minggu_awal = isset($_GET['minggu_awal']) ? $_GET['minggu_awal'] : '';
$minggu_akhir = isset($_GET['minggu_akhir']) ? $_GET['minggu_akhir'] : '';

$data_laporan = [];

if ($filter === 'harian') {
    // Laporan harian: ambil nama siswa dari users lewat siswa_profile
    $stmt = $pdo->prepare('
        SELECT 
            k.id, 
            u.nama_lengkap AS siswa_nama, 
            k.jam, 
            k.catatan_sesi
        FROM konseling k
        JOIN siswa_profile sp ON k.siswa_id = sp.id
        JOIN users u ON sp.user_id = u.id
        WHERE k.guru_id = ? 
          AND k.tanggal = ?
        ORDER BY k.jam ASC
    ');
    $stmt->execute([$_SESSION['user_id'], $tanggal]);
    $data_laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($filter === 'mingguan' && $minggu_awal && $minggu_akhir) {
    // Laporan mingguan: ambil nama siswa dari users lewat siswa_profile
    $stmt = $pdo->prepare('
        SELECT 
            k.id, 
            k.tanggal, 
            u.nama_lengkap AS siswa_nama, 
            k.jam, 
            k.catatan_sesi
        FROM konseling k
        JOIN siswa_profile sp ON k.siswa_id = sp.id
        JOIN users u ON sp.user_id = u.id
        WHERE k.guru_id = ? 
          AND k.tanggal BETWEEN ? AND ?
        ORDER BY k.tanggal ASC, k.jam ASC
    ');
    $stmt->execute([$_SESSION['user_id'], $minggu_awal, $minggu_akhir]);
    $data_laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($filter === 'bulanan') {
    // Laporan bulanan: ambil nama siswa dari users lewat siswa_profile
    $stmt = $pdo->prepare('
        SELECT 
            k.id, 
            k.tanggal, 
            u.nama_lengkap AS siswa_nama, 
            k.jam, 
            k.catatan_sesi
        FROM konseling k
        JOIN siswa_profile sp ON k.siswa_id = sp.id
        JOIN users u ON sp.user_id = u.id
        WHERE k.guru_id = ? 
          AND DATE_FORMAT(k.tanggal, "%Y-%m") = ?
        ORDER BY k.tanggal ASC, k.jam ASC
    ');
    $stmt->execute([$_SESSION['user_id'], $bulan]);
    $data_laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php include 'header.php'; ?>
<h3>Laporan dan Dokumentasi</h3>

<div class="card mb-4">
    <div class="card-header">Filter Laporan</div>
    <div class="card-body">
        <form method="GET" action="laporan.php" class="row g-3">
            <div class="col-md-3">
                <select class="form-select" name="filter" id="filterSelect" onchange="onFilterChange()">
                    <option value="harian" <?= $filter === 'harian' ? 'selected' : '' ?>>Harian</option>
                    <option value="mingguan" <?= $filter === 'mingguan' ? 'selected' : '' ?>>Mingguan</option>
                    <option value="bulanan" <?= $filter === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                </select>
            </div>
            <div class="col-md-3" id="div-harian" style="<?= $filter === 'harian' ? '' : 'display:none;' ?>">
                <input type="date" class="form-control" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
            </div>
            <div class="col-md-3" id="div-mingguan" style="<?= $filter === 'mingguan' ? '' : 'display:none;' ?>">
                <label for="minggu_awal" class="form-label">Dari</label>
                <input type="date" class="form-control" id="minggu_awal" name="minggu_awal" value="<?= htmlspecialchars($minggu_awal) ?>">
            </div>
            <div class="col-md-3" id="div-minggu-akhir" style="<?= $filter === 'mingguan' ? '' : 'display:none;' ?>">
                <label for="minggu_akhir" class="form-label">Sampai</label>
                <input type="date" class="form-control" id="minggu_akhir" name="minggu_akhir" value="<?= htmlspecialchars($minggu_akhir) ?>">
            </div>
            <div class="col-md-3" id="div-bulanan" style="<?= $filter === 'bulanan' ? '' : 'display:none;' ?>">
                <input type="month" class="form-control" name="bulan" value="<?= htmlspecialchars($bulan) ?>">
            </div>
            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Laporan -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Hasil Laporan <?= ucfirst($filter) ?></span>
        <a href="export_pdf.php?filter=<?= $filter ?>&tanggal=<?= $tanggal ?>&minggu_awal=<?= $minggu_awal ?>&minggu_akhir=<?= $minggu_akhir ?>&bulan=<?= $bulan ?>" class="btn btn-sm btn-success">Ekspor ke PDF</a>
    </div>
    <div class="card-body">
        <?php if (count($data_laporan) === 0): ?>
            <p>Tidak ada data untuk periode ini.</p>
        <?php else: ?>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <?php if ($filter !== 'harian'): ?>
                            <th>Tanggal</th>
                        <?php endif; ?>
                        <th>Jam</th>
                        <th>Siswa</th>
                        <th>Catatan Sesi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data_laporan as $d): ?>
                    <tr>
                        <?php if ($filter !== 'harian'): ?>
                            <td><?= date('d-m-Y', strtotime($d['tanggal'])) ?></td>
                        <?php endif; ?>
                        <td><?= substr($d['jam'], 0, 5) ?></td>
                        <td><?= htmlspecialchars($d['siswa_nama']) ?></td>
                        <td><?= nl2br(htmlspecialchars($d['catatan_sesi'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
function onFilterChange() {
    const filter = document.getElementById('filterSelect').value;
    document.getElementById('div-harian').style.display = (filter === 'harian') ? '' : 'none';
    document.getElementById('div-mingguan').style.display = (filter === 'mingguan') ? '' : 'none';
    document.getElementById('div-minggu-akhir').style.display = (filter === 'mingguan') ? '' : 'none';
    document.getElementById('div-bulanan').style.display = (filter === 'bulanan') ? '' : 'none';
}
</script>

<?php include 'footer.php'; ?>
