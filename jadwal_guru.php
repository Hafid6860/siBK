<?php
// jadwal_guru.php
require_once 'config.php';
if (!isLoggedIn() || !isGuru()) {
    header('Location: login.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$notice = '';
$edit_data = null;

// Jika tombol Edit diklik (tampilkan form edit)
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $stmtEdit = $pdo->prepare('SELECT id, tanggal, jam FROM jadwal_konseling WHERE id = ? AND guru_id = ?');
    $stmtEdit->execute([$edit_id, $_SESSION['user_id']]);
    $edit_data = $stmtEdit->fetch(PDO::FETCH_ASSOC);
    if (!$edit_data) {
        $notice = 'Data jadwal tidak ditemukan atau Anda tidak berhak mengedit.';
    }
}

// Proses update jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_jadwal'])) {
    $id = intval($_POST['id']);
    $tanggal = $_POST['tanggal'];
    $jam = $_POST['jam'];
    if ($tanggal === '' || $jam === '') {
        $notice = 'Tanggal dan jam wajib diisi.';
    } else {
        // Cek status sebelum update (hanya status = open bisa diedit)
        $stmtCheck = $pdo->prepare('SELECT status FROM jadwal_konseling WHERE id = ? AND guru_id = ?');
        $stmtCheck->execute([$id, $_SESSION['user_id']]);
        $rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if ($rowCheck && $rowCheck['status'] === 'open') {
            $stmtUpd = $pdo->prepare('UPDATE jadwal_konseling SET tanggal = ?, jam = ? WHERE id = ?');
            $stmtUpd->execute([$tanggal, $jam, $id]);
            $notice = 'Jadwal berhasil diperbarui.';
            // Setelah edit, bersihkan mode edit
            $edit_data = null;
        } else {
            $notice = 'Tidak dapat mengedit jadwal yang sudah dipesan atau tidak ditemukan.';
        }
    }
}

// Proses tambah jadwal baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_jadwal'])) {
    $tanggal = $_POST['tanggal'];
    $jam = $_POST['jam'];
    if ($tanggal === '' || $jam === '') {
        $notice = 'Tanggal dan jam wajib diisi.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO jadwal_konseling (guru_id, tanggal, jam) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $tanggal, $jam]);
        $notice = 'Jadwal berhasil ditambahkan.';
    }
}

// Proses hapus jadwal
if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Hanya hapus jika status = open
    $stmtCheck = $pdo->prepare('SELECT status FROM jadwal_konseling WHERE id = ? AND guru_id = ?');
    $stmtCheck->execute([$id, $_SESSION['user_id']]);
    $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['status'] === 'open') {
        $stmtDel = $pdo->prepare('DELETE FROM jadwal_konseling WHERE id = ?');
        $stmtDel->execute([$id]);
        $notice = 'Jadwal berhasil dihapus.';
    } else {
        $notice = 'Tidak dapat menghapus jadwal yang sudah dipesan atau tidak ditemukan.';
    }
}

// Ambil daftar semua jadwal guru ini (termasuk nama siswa jika sudah dipesan)
$stmt = $pdo->prepare('
    SELECT 
        jk.id,
        jk.tanggal,
        jk.jam,
        jk.status,
        u.nama_lengkap AS siswa_nama
    FROM jadwal_konseling jk
    LEFT JOIN siswa_profile sp ON jk.siswa_id = sp.id
    LEFT JOIN users u ON sp.user_id = u.id
    WHERE jk.guru_id = ?
    ORDER BY jk.tanggal DESC, jk.jam DESC
');
$stmt->execute([$_SESSION['user_id']]);
$list_jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>
<h3>Manajemen Jadwal Konseling</h3>

<?php if ($notice): ?>
    <div class="alert alert-info"><?= htmlspecialchars($notice) ?></div>
<?php endif; ?>

<!-- Form Tambah / Edit Jadwal -->
<div class="card mb-4">
    <div class="card-header">
        <?= $edit_data ? 'Edit Jadwal Konseling' : 'Tambah Jadwal Konseling' ?>
    </div>
    <div class="card-body">
        <form method="POST" action="jadwal_guru.php<?= $edit_data ? '?action=edit&id=' . $edit_data['id'] : '' ?>">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <?php endif; ?>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal"
                           value="<?= $edit_data ? htmlspecialchars($edit_data['tanggal']) : '' ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="jam" class="form-label">Jam</label>
                    <input type="time" class="form-control" id="jam" name="jam"
                           value="<?= $edit_data ? htmlspecialchars($edit_data['jam']) : '' ?>" required>
                </div>
                <div class="col-md-4 align-self-end mb-3">
                    <?php if ($edit_data): ?>
                        <button type="submit" name="update_jadwal" class="btn btn-primary">Update</button>
                        <a href="jadwal_guru.php" class="btn btn-secondary">Batal</a>
                    <?php else: ?>
                        <button type="submit" name="tambah_jadwal" class="btn btn-success">Tambahkan</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Daftar Jadwal -->
<div class="card mb-4">
    <div class="card-header">Daftar Seluruh Jadwal</div>
    <div class="card-body">
        <?php if (count($list_jadwal) === 0): ?>
            <p>Belum ada jadwal.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Status</th>
                        <th>Siswa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($list_jadwal as $jd): ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($jd['tanggal'])) ?></td>
                        <td><?= substr($jd['jam'], 0, 5) ?></td>
                        <td><?= ucfirst($jd['status']) ?></td>
                        <td><?= $jd['siswa_nama'] ? htmlspecialchars($jd['siswa_nama']) : '-' ?></td>
                        <td>
                            <?php if ($jd['status'] === 'open'): ?>
                                <a href="jadwal_guru.php?action=edit&id=<?= $jd['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="jadwal_guru.php?action=delete&id=<?= $jd['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus jadwal ini?');">Hapus</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>Edit</button>
                                <button class="btn btn-sm btn-secondary" disabled>Hapus</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
