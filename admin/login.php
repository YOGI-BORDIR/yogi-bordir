<?php
session_start();
require_once '../config/database.php';

if(isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admin WHERE username = ? AND password = MD5(?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if($admin) {
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_nama'] = $admin['nama_lengkap'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
        $db->close();
    } else {
        $error = 'Harap isi semua kolom!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Yogi Bordir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #2d5a27 0%, #4a8c3f 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-card { border: none; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
        .btn-login { background: #2d5a27; color: white; border: none; border-radius: 50px; padding: 12px; font-weight: 600; width: 100%; transition: all .3s; }
        .btn-login:hover { background: #4a8c3f; color: white; transform: translateY(-2px); }
        .form-control:focus { border-color: #4a8c3f; box-shadow: 0 0 0 .25rem rgba(74,140,63,.25); }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card login-card p-4">
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px;background:linear-gradient(135deg,#2d5a27,#4a8c3f)">
                        <i class="bi bi-scissors text-white fs-3"></i>
                    </div>
                    <h4 class="fw-bold mb-0">Yogi Bordir</h4>
                    <p class="text-muted small">Panel Admin</p>
                </div>

                <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="../index.php" class="text-muted small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Website
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
