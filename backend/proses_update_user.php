
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
// Izinkan admin & petugas memproses update
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = intval($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = htmlspecialchars(trim($_POST['role'] ?? ''));

    // Validasi Data Wajib
    if ($id <= 0 || empty($username) || empty($role)) {
        echo "<script>
            alert('Username dan Role wajib diisi!');
            window.history.back();
        </script>";
        exit();
    }

    // Cek apakah password diisi atau dikosongkan
    if (!empty($password)) {
        // Jika password diisi, update username, password, dan role
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $query = "UPDATE user SET username = :username, password = :password, role = :role WHERE id = :id";
        $stmt  = $db->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
    } else {
        // Jika password kosong, update username dan role saja
        $query = "UPDATE user SET username = :username, role = :role WHERE id = :id";
        $stmt  = $db->prepare($query);
    }

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        header("Location: tabel_user.php?status=updated");
        exit();
    } else {
        echo "<script>alert('Gagal memperbarui data user!'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: tabel_user.php");
    exit();
}