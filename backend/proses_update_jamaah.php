
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = intval($_POST['id'] ?? 0);
    $nik           = htmlspecialchars(trim($_POST['nik'] ?? ''));
    $nama_lengkap  = htmlspecialchars(trim($_POST['nama_lengkap'] ?? ''));
    $jenis_kelamin = htmlspecialchars(trim($_POST['jenis_kelamin'] ?? ''));
    $no_hp         = htmlspecialchars(trim($_POST['no_hp'] ?? ''));
    $alamat        = htmlspecialchars(trim($_POST['alamat'] ?? ''));

    if (!$id || empty($nik) || empty($nama_lengkap) || empty($jenis_kelamin)) {
        echo "<script>alert('NIK, Nama Lengkap, dan Jenis Kelamin wajib diisi!'); window.history.back();</script>";
        exit();
    }

    $query = "UPDATE jamaah 
              SET nik = :nik, 
                  nama_lengkap = :nama_lengkap, 
                  jenis_kelamin = :jenis_kelamin, 
                  no_hp = :no_hp, 
                  alamat = :alamat 
              WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':nik', $nik);
    $stmt->bindParam(':nama_lengkap', $nama_lengkap);
    $stmt->bindParam(':jenis_kelamin', $jenis_kelamin);
    $stmt->bindParam(':no_hp', $no_hp);
    $stmt->bindParam(':alamat', $alamat);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: tabel_jamaah.php?status=updated");
        exit();
    } else {
        echo "<script>alert('Gagal memperbarui data jamaah!'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: tabel_jamaah.php");
    exit();
}
?>