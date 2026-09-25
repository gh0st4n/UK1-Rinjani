
<?php
require_once "database/connection.php";

$db = (new Database())->getConnection();

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik           = htmlspecialchars(trim($_POST['nik']));
    $nama_lengkap  = htmlspecialchars(trim($_POST['nama_lengkap']));
    $jenis_kelamin = htmlspecialchars(trim($_POST['jenis_kelamin']));
    $no_hp         = htmlspecialchars(trim($_POST['no_hp']));
    $alamat        = htmlspecialchars(trim($_POST['alamat']));
    $username      = htmlspecialchars(trim($_POST['username']));
    $password      = htmlspecialchars(trim($_POST['password']));

    if (empty($nik) || empty($nama_lengkap) || empty($jenis_kelamin) || empty($username) || empty($password)) {
        $error = "NIK, Nama Lengkap, Jenis Kelamin, Username, dan Password wajib diisi!";
    } else {
        try {
            // 1. Cek apakah Username sudah terdaftar
            $checkUser = $db->prepare("SELECT id FROM user WHERE username = ?");
            $checkUser->execute([$username]);

            // 2. Cek apakah NIK sudah terdaftar
            $checkNik = $db->prepare("SELECT id FROM jamaah WHERE nik = ?");
            $checkNik->execute([$nik]);

            if ($checkUser->rowCount() > 0) {
                $error = "Username sudah digunakan, silakan pilih username lain!";
            } else if ($checkNik->rowCount() > 0) {
                $error = "NIK sudah terdaftar! Silakan gunakan NIK lain atau langsung login.";
            } else {
                $db->beginTransaction();

                // Step A: Insert data user awal untuk dapat ID user
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmtUser = $db->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, 'jamaah')");
                $stmtUser->execute([$username, $hashedPassword]);
                $userId = $db->lastInsertId();

                // Step B: Insert data jamaah membawa user_id
                $stmtJamaah = $db->prepare("INSERT INTO jamaah (user_id, nik, nama_lengkap, jenis_kelamin, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtJamaah->execute([$userId, $nik, $nama_lengkap, $jenis_kelamin, $no_hp, $alamat]);
                $jamaahId = $db->lastInsertId();

                // Step C: Update balik jamaah_id ke tabel user
                $updateUser = $db->prepare("UPDATE user SET jamaah_id = ? WHERE id = ?");
                $updateUser->execute([$jamaahId, $userId]);

                $db->commit();
                $success = "Registrasi berhasil! Mengalihkan ke halaman login...";
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $error = "Gagal mendaftar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Jamaah - Kemenhaj Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if ($success): ?>
        <meta http-equiv="refresh" content="2;url=login.php">
    <?php endif; ?>
    <style>
        body { background: linear-gradient(135deg, #064e3b 0%, #022c22 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 0; }
        .card-register { background: #fff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); width: 100%; max-width: 480px; padding: 35px; }
        .btn-gold { background: #d4af37; color: #064e3b; font-weight: 700; border: none; }
        .btn-gold:hover { background: #c29d26; color: #fff; }
    </style>
</head>
<body>
<div class="card-register text-center">
    <div class="mb-3 text-warning"><i class="fas fa-user-plus fa-3x" style="color: #064e3b;"></i></div>
    <h4 class="fw-bold text-success mb-1">REGISTRASI JAMAAH</h4>
    <p class="text-muted small mb-4">Isi data lengkap Anda untuk mendaftar layanan travel</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?= $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success py-3 small fw-bold">
            <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
            <?= $success; ?>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "login.php";
            }, 2000);
        </script>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST">
        <div class="mb-3 text-start">
            <label class="form-label small fw-bold">NIK (Nomor Induk Kependudukan)</label>
            <input type="text" name="nik" class="form-control" placeholder="Masukkan 16 digit NIK" required>
        </div>
        <div class="mb-3 text-start">
            <label class="form-label small fw-bold">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" required>
        </div>
        <div class="mb-3 text-start">
            <label class="form-label small fw-bold">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-select" required>
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>
        </div>
        <div class="mb-3 text-start">
            <label class="form-label small fw-bold">No. Telepon / WhatsApp</label>
            <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 08123456789">
        </div>
        <div class="mb-3 text-start">
            <label class="form-label small fw-bold">Alamat Lengkap</label>
            <textarea name="alamat" class="form-control" rows="2" placeholder="Masukkan alamat domisili"></textarea>
        </div>
        <div class="mb-3 text-start">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Buat username" required>
        </div>
        <div class="mb-4 text-start">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Buat password" required>
        </div>
        <button type="submit" class="btn btn-gold w-100 py-2 rounded-3 mb-3">DAFTAR SEKARANG</button>
    </form>
    <?php endif; ?>

    <div class="text-center mt-2 pt-3 border-top">
        <p class="small text-muted mb-0">Sudah punya akun? <a href="login.php" class="text-success fw-bold text-decoration-none">Masuk di sini</a></p>
    </div>
</div>
</body>
</html>