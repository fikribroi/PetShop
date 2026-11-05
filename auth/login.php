<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Jika sudah login, langsung lempar ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: " . base_url('index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Silakan masukkan username dan password!";
    } else {
        // Query cari user berdasarkan username
        $stmt = mysqli_prepare($conn, "SELECT id_user, username, password, nama_lengkap, role FROM users WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Verifikasi password (Bcrypt, MD5, atau plaintext fallback untuk kemudahan pengujian)
            $isValid = false;
            if (password_verify($password, $user['password'])) {
                $isValid = true;
            } elseif (md5($password) === $user['password']) {
                $isValid = true;
            } elseif ($password === $user['password']) {
                $isValid = true;
            }

            if ($isValid) {
                $_SESSION['user_id']      = $user['id_user'];
                $_SESSION['username']     = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role']         = $user['role'];

                setFlashMessage('success', 'Selamat datang kembali, ' . $user['nama_lengkap'] . '!');
                header("Location: " . base_url('index.php'));
                exit;
            } else {
                $error = "Password yang Anda masukkan salah!";
            }
        } else {
            $error = "Username tidak ditemukan dalam database!";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pet Shop & Penitipan Hewan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            max-width: 440px;
            width: 100%;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="d-inline-flex p-3 rounded-circle bg-white text-teal shadow-sm mb-3" style="color: #0d9488;">
            <i class="bi bi-shield-heart-fill fs-1"></i>
        </div>
        <h4 class="fw-bold mb-1">PetCare Studio</h4>
        <p class="mb-0 small text-white-50">Sistem Pet Shop & Penitipan Hewan Peliharaan</p>
    </div>

    <div class="p-4 p-md-5">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div class="small"><?= htmlspecialchars($error) ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php showFlashMessage(); ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold small text-muted">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold small text-muted">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
            </button>
        </form>

        <div class="card bg-light border-0 rounded-3 p-3">
            <div class="small text-muted fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Akun Login Default Demo:</div>
            <div class="small text-secondary font-monospace">
                <div>• Username: <strong>admin</strong> | Password: <strong>admin123</strong> (Admin)</div>
                <div>• Username: <strong>petugas</strong> | Password: <strong>admin123</strong> (Staff)</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

