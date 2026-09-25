
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";
require_once "classes/Paket.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = intval($_POST['id'] ?? 0);
    $nama_paket = htmlspecialchars(trim($_POST['nama_paket'] ?? ''));
    // Membaca jenis_paket atau fallback ke jenis
    $jenis      = htmlspecialchars(trim($_POST['jenis_paket'] ?? $_POST['jenis'] ?? ''));
    $harga      = intval($_POST['harga'] ?? 0);
    $durasi     = intval($_POST['durasi'] ?? 0); // <--- TANGKAP DURASI
    $kuota      = intval($_POST['kuota'] ?? 0);
    $deskripsi  = htmlspecialchars(trim($_POST['deskripsi'] ?? ''));

    // 1. Validasi Input Utama
    if ($id <= 0 || empty($nama_paket) || empty($jenis)) {
        echo "<script>
            alert('Data ID, Nama Paket, dan Jenis Paket wajib diisi!');
            window.history.back();
        </script>";
        exit();
    }

    // 2. Validasi Angka Negatif, Durasi, & Kuota
    if ($harga < 0 || $durasi < 1 || $kuota < 1) { // <--- TAMBAH VALIDASI DURASI
        echo "<script>
            alert('Harga tidak boleh negatif, Durasi minimal 1 Hari, dan Kuota minimal 1 Jamaah!');
            window.history.back();
        </script>";
        exit();
    }

    $paketObj = new Paket($db);
    
    // 3. Masukkan $durasi ke dalam method update()
    if ($paketObj->update($id, $nama_paket, $jenis, $harga, $durasi, $kuota, $deskripsi)) {
        header("Location: tabel_paket.php?status=updated");
        exit();
    } else {
        echo "<script>alert('Gagal memperbarui data paket!'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: tabel_paket.php");
    exit();
}