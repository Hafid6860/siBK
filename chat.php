<?php
// chat.php
require_once 'config.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Jika guru, tampilkan dropdown semua siswa (berdasarkan nama di users)
if ($role === 'guru') {
    $stmt0 = $pdo->query('
        SELECT sp.id AS siswa_id, u.nama_lengkap 
        FROM siswa_profile sp 
        JOIN users u ON sp.user_id = u.id 
        WHERE u.role = "siswa" 
        ORDER BY u.nama_lengkap ASC
    ');
    $daftar_siswa = $stmt0->fetchAll(PDO::FETCH_ASSOC);
    $selected_siswa = isset($_GET['siswa_id']) 
        ? intval($_GET['siswa_id']) 
        : (isset($daftar_siswa[0]['siswa_id']) ? $daftar_siswa[0]['siswa_id'] : 0);
} else {
    // Jika siswa, cari ID siswa_profile
    $stmt1 = $pdo->prepare('SELECT id FROM siswa_profile WHERE user_id = ?');
    $stmt1->execute([$user_id]);
    $row = $stmt1->fetch(PDO::FETCH_ASSOC);
    $siswa_id = $row['id'];

    // Cari ID guru (asumsi hanya satu guru BK)
    $stmt2 = $pdo->query('SELECT id FROM users WHERE role = "guru" LIMIT 1');
    $guru = $stmt2->fetch(PDO::FETCH_ASSOC);
    $selected_siswa = $siswa_id;
}

// Jika pengiriman pesan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan'])) {
    $pesan = trim($_POST['pesan']);
    if ($pesan !== '') {
        if ($role === 'guru') {
            $tujuan_siswa = intval($_POST['siswa_id']);
            $stmtInsert = $pdo->prepare('
                INSERT INTO chat (guru_id, siswa_id, pengirim, pesan)
                VALUES (?, ?, "guru", ?)
            ');
            $stmtInsert->execute([$user_id, $tujuan_siswa, $pesan]);
        } else {
            // siswa
            $stmtG = $pdo->query('SELECT id FROM users WHERE role = "guru" LIMIT 1');
            $guru_data = $stmtG->fetch(PDO::FETCH_ASSOC);
            $guru_id = $guru_data['id'];
            $stmtInsert = $pdo->prepare('
                INSERT INTO chat (guru_id, siswa_id, pengirim, pesan)
                VALUES (?, ?, "siswa", ?)
            ');
            $stmtInsert->execute([$guru_id, $siswa_id, $pesan]);
        }
        // Redirect agar tidak dobel submit
        if ($role === 'guru') {
            header("Location: chat.php?siswa_id=$selected_siswa");
        } else {
            header("Location: chat.php");
        }
        exit;
    }
}

// Ambil pesan chat (50 terakhir) antara guru <-> siswa terpilih
if ($role === 'guru') {
    $siswa_id = $selected_siswa;
    $stmt3 = $pdo->prepare('
        SELECT c.*, u.username AS pengirim_user 
        FROM chat c
        JOIN users u 
          ON (c.pengirim = "guru" AND u.id = c.guru_id)
          OR (c.pengirim = "siswa" AND u.id = (SELECT user_id FROM siswa_profile WHERE id = c.siswa_id))
        WHERE c.guru_id = ? AND c.siswa_id = ?
        ORDER BY c.waktu ASC
        LIMIT 50
    ');
    $stmt3->execute([$user_id, $siswa_id]);
    $chats = $stmt3->fetchAll(PDO::FETCH_ASSOC);
} else {
    // siswa
    $stmtG = $pdo->query('SELECT id FROM users WHERE role = "guru" LIMIT 1');
    $guru = $stmtG->fetch(PDO::FETCH_ASSOC);
    $guru_id = $guru['id'];
    $stmt3 = $pdo->prepare('
        SELECT c.*, u.username AS pengirim_user 
        FROM chat c
        JOIN users u 
          ON (c.pengirim = "guru" AND u.id = c.guru_id)
          OR (c.pengirim = "siswa" AND u.id = (SELECT user_id FROM siswa_profile WHERE id = c.siswa_id))
        WHERE c.guru_id = ? AND c.siswa_id = ?
        ORDER BY c.waktu ASC
        LIMIT 50
    ');
    $stmt3->execute([$guru_id, $siswa_id]);
    $chats = $stmt3->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php include 'header.php'; ?>
<h3>Chat Rahasia</h3>

<div class="row">
    <div class="col-md-4 mb-4">
        <?php if ($role === 'guru'): ?>
            <form method="GET" action="chat.php">
                <div class="mb-3">
                    <label for="siswa_id" class="form-label">Pilih Siswa</label>
                    <select name="siswa_id" id="siswa_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($daftar_siswa as $s): ?>
                            <option value="<?= $s['siswa_id'] ?>" <?= $s['siswa_id'] === $selected_siswa ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nama_lengkap']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        <?php endif; ?>

        <div class="border p-3" style="height: 500px; overflow-y: scroll; background-color: #f8f9fa;">
            <?php if (empty($chats)): ?>
                <p class="text-center text-muted mt-5">Belum ada pesan.</p>
            <?php else: ?>
                <?php foreach ($chats as $c): ?>
                    <?php if ($c['pengirim'] === 'guru'): ?>
                        <div class="mb-2 text-end">
                            <small class="text-primary">Guru:</small>
                            <p class="d-inline-block bg-primary text-white p-2 rounded"><?= nl2br(htmlspecialchars($c['pesan'])) ?></p>
                            <br>
                            <small class="text-muted"><?= date('d-m-Y H:i', strtotime($c['waktu'])) ?></small>
                        </div>
                    <?php else: ?>
                        <div class="mb-2 text-start">
                            <small class="text-success">Siswa:</small>
                            <p class="d-inline-block bg-success text-white p-2 rounded"><?= nl2br(htmlspecialchars($c['pesan'])) ?></p>
                            <br>
                            <small class="text-muted"><?= date('d-m-Y H:i', strtotime($c['waktu'])) ?></small>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-8 mb-4">
        <form method="POST" action="chat.php<?= $role === 'guru' ? '?siswa_id=' . $selected_siswa : '' ?>">
            <?php if ($role === 'guru'): ?>
                <input type="hidden" name="siswa_id" value="<?= $selected_siswa ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label for="pesan" class="form-label">Tulis Pesan</label>
                <textarea class="form-control" id="pesan" name="pesan" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kirim</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
