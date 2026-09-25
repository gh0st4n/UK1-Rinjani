
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";
require_once "classes/Paket.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = htmlspecialchars(trim($_POST['password'] ?? ''));
    $role     = 'petugas';

    // 1. Validasi Input Tidak Boleh Kosong
   if (empty($username) || empty($password)) {
        echo "<script>
            alert('Semua field wajib diisi!');
            window.history.back();
        </script>";
        exit();
    }

    // 2. Cek apakah username sudah terdaftar
    $stmtCek = $db->prepare("SELECT * FROM user WHERE username = :username");
    $stmtCek->bindParam(':username', $username);
    $stmtCek->execute();

    if ($stmtCek->fetch(PDO::FETCH_ASSOC)) {
        echo "<script>
            alert('Username sudah digunakan! Silakan gunakan username lain.');
            window.history.back();
        </script>";
        exit();
    }

    // 3. Proses Tambah User Langsung ke Database
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO user (username, password, role) VALUES (:username, :password, :role)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':role', $role);

    if ($stmt->execute()) {
        header("Location: tabel_user.php?status=success");
        exit();
    } else {
        echo "<script>alert('Gagal menambah data user!'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: tabel_user.php");
    exit();
}