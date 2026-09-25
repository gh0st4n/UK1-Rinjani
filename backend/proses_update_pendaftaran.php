
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id               = intval($_POST['id'] ?? 0);
    $jamaah_id        = intval($_POST['jamaah_id'] ?? 0);
    $paket_id         = intval($_POST['paket_id'] ?? 0);
    $keberangkatan_id = !empty($_POST['keberangkatan_id']) ? intval($_POST['keberangkatan_id']) : null;
    
    // PERBAIKAN DI SINI: disesuaikan dengan name="tgl_daftar" dari form HTML
    $tgl_daftar       = htmlspecialchars(trim($_POST['tgl_daftar'] ?? '')); 
    $status           = htmlspecialchars(trim($_POST['status'] ?? ''));

    if (!$id || !$jamaah_id || !$paket_id || empty($tgl_daftar) || empty($status)) {
        echo "<script>alert('Jamaah, Paket, Tanggal Daftar, dan Status wajib diisi!'); window.history.back();</script>";
        exit();
    }

    $query = "UPDATE pendaftaran 
              SET jamaah_id = :jamaah_id, 
                  paket_id = :paket_id, 
                  keberangkatan_id = :keberangkatan_id, 
                  tgl_daftar = :tgl_daftar, 
                  status = :status 
              WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':jamaah_id', $jamaah_id, PDO::PARAM_INT);
    $stmt->bindParam(':paket_id', $paket_id, PDO::PARAM_INT);
    
    if (is_null($keberangkatan_id)) {
        $stmt->bindValue(':keberangkatan_id', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':keberangkatan_id', $keberangkatan_id, PDO::PARAM_INT);
    }

    $stmt->bindParam(':tgl_daftar', $tgl_daftar);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: tabel_pendaftaran.php?status=updated");
        exit();
    } else {
        echo "<script>alert('Gagal memperbarui data pendaftaran!'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: tabel_pendaftaran.php");
    exit();
}
?>