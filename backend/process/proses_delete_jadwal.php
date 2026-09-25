<?php
require_once "../connection.php";
require_once "../classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin']);

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Hapus data keberangkatan berdasarkan Primary Key 'id'
        $stmt = $db->prepare("DELETE FROM keberangkatan WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect kembali ke tabel_keberangkatan.php
        header("Location: ../tabel_keberangkatan.php?status=deleted");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Gagal menghapus data keberangkatan!'); window.location.href='../tabel_keberangkatan.php';</script>";
        exit();
    }
} else {
    header("Location: ../tabel_keberangkatan.php");
    exit();
}
