<?php
require_once "../connection.php";
require_once "../classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin']);

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Hapus akun petugas berdasarkan Primary Key 'id' dan pastikan role-nya adalah 'petugas'
        $stmt = $db->prepare("DELETE FROM user WHERE id = :id AND role = 'petugas'");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect kembali ke tabel_user.php di luar folder process
        header("Location: ../tabel_user.php?status=deleted");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Gagal menghapus data petugas!'); window.location.href='../tabel_user.php';</script>";
        exit();
    }
} else {
    header("Location: ../tabel_user.php");
    exit();
}
