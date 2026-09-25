
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";
require_once "classes/Paket.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_paket = htmlspecialchars(trim($_POST['nama_paket'] ?? ''));
    $jenis      = htmlspecialchars(trim($_POST['jenis_paket'] ?? '')); 
    $harga      = intval($_POST['harga'] ?? 0);
    $durasi     = intval($_POST['durasi'] ?? 0); // <-- BARU: Tangkap variabel durasi
    $kuota      = intval($_POST['kuota'] ?? 0);
    $deskripsi  = htmlspecialchars(trim($_POST['deskripsi'] ?? ''));

    // 1. Validasi Input Tidak Boleh Kosong
    if (empty($nama_paket) || empty($jenis)) {
        echo "<script>
            alert('Nama Paket dan Jenis Paket wajib diisi!');
            window.history.back();
        </script>";
        exit();
    }

    // 2. Validasi Angka Negatif, Durasi, & Kuota (DIPERBARUI)
    if ($harga < 0 || $durasi < 1 || $kuota < 1) { // <-- BARU: Tambah validasi durasi min 1
        echo "<script>
            alert('Harga tidak boleh negatif, Durasi minimal 1 Hari, dan Kuota minimal 1 Jamaah!');
            window.history.back();
        </script>";
        exit();
    }

    $paketObj = new Paket($db);
    // <-- DIPERBARUI: Masukkan $durasi ke dalam parameter create()
    if ($paketObj->create($nama_paket, $jenis, $harga, $durasi, $kuota, $deskripsi)) {
        header("Location: tabel_paket.php?status=success");
        exit();
    } else {
        echo "<script>alert('Gagal menambah data paket!'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: tabel_paket.php");
    exit();

}