<?php
// login.php
require_once 'config.php';

// Jika sudah login, redirect sesuai role
if (isLoggedIn()) {
    if (isGuru()) {
        header('Location: dashboard_guru.php');
        exit;
    } elseif (isSiswa()) {
        header('Location: dashboard_siswa.php');
        exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

            if ($user['role'] === 'guru') {
                header('Location: dashboard_guru.php');
            } else {
                header('Location: dashboard_siswa.php');
            }
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>

<?php include 'header.php'; ?>
<h3>Login SiBK</h3>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="POST" action="login.php">
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username"
               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
</form>
<?php include 'footer.php'; ?>
