
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pendaftaran_id = intval($_POST['pendaftaran_id'] ?? 0);
    $nominal        = floatval($_POST['nominal'] ?? 0);
    $tanggal_bayar  = htmlspecialchars(trim($_POST['tanggal_bayar'] ?? ''));
    $sisa_pembayaran = htmlspecialchars(trim($_POST['sisa_pembayaran']));
    $status         = htmlspecialchars(trim($_POST['status'] ?? 'Valid'));

    // 1. Validasi Input Utama
    if ($pendaftaran_id <= 0 || empty($tanggal_bayar)) {
        echo "<script>
            alert('Jamaah/Pendaftaran dan Tanggal Bayar wajib dipilih!');
            window.history.back();
        </script>";
        exit();
    }

    // 2. Validasi Nominal Tidak Boleh Kurang Dari 1
    if ($nominal <= 0) {
        echo "<script>
            alert('Nominal pembayaran harus lebih dari 0!');
            window.history.back();
        </script>";
        exit();
    }


    // 4. Insert Data ke Database
    try {
        $query = "INSERT INTO pembayaran (pendaftaran_id, tanggal_bayar, nominal, sisa_pembayaran, status) 
                  VALUES (:pendaftaran_id, :tanggal_bayar, :nominal, :sisa_pembayaran, :status)";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':pendaftaran_id', $pendaftaran_id, PDO::PARAM_INT);
        $stmt->bindParam(':tanggal_bayar', $tanggal_bayar);
        $stmt->bindParam(':nominal', $nominal);
        $stmt->bindParam(':sisa_pembayaran', $sisa_pembayaran);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            header("Location: tabel_pembayaran.php?status=success");
            exit();
        } else {
            echo "<script>alert('Gagal menambah data pembayaran!'); window.history.back();</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error Database: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: tabel_pembayaran.php");
    exit();
}
?>