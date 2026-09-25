
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));

    if ($auth->login($username, $password)) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Kemenhaj Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #064e3b 0%, #022c22 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-login { background: #fff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); width: 100%; max-width: 400px; padding: 40px; }
        .btn-gold { background: #d4af37; color: #064e3b; font-weight: 700; border: none; }
        .btn-gold:hover { background: #c29d26; color: #fff; }
    </style>
</head>
<body>
<div class="card-login text-center">
    <div class="mb-3 text-warning"><i class="fas fa-kaaba fa-3x" style="color: #064e3b;"></i></div>
    <h4 class="fw-bold text-success mb-1">KEMENHAJ PANEL</h4>
    <p class="text-muted small mb-4">Masuk untuk mengelola data travel</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3 text-start">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
        </div>
        <div class="mb-4 text-start">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>
        <button type="submit" class="btn btn-gold w-100 py-2 rounded-3 mb-3">LOGIN</button>
    </form>

    <div class="text-center mt-3 pt-3 border-top">
        <p class="small text-muted mb-0">Belum punya akun jamaah? <a href="register.php" class="text-success fw-bold text-decoration-none">Daftar Akun</a></p>
    </div>
</div>
</body>


</html>