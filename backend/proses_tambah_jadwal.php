<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pendaftaran_id = $_POST['pendaftaran_id'] ?? null;
    $paket_id = $_POST['paket_id'] ?? null;
    $tanggal_berangkat = $_POST['tanggal_berangkat'] ?? null;
    $tgl_kepulangan = !empty($_POST['tgl_kepulangan']) ? $_POST['tgl_kepulangan'] : null;
    $maskapai = $_POST['maskapai'] ?? null;
    $embarkasi = $_POST['embarkasi'] ?? null;
    $kuota_penerbangan = $_POST['kuota'] ?? null;
    $keterangan = $_POST['keterangan'] ?? null;

    if (!$paket_id || !$tanggal_berangkat || !$maskapai || !$embarkasi || !$kuota_penerbangan) {
        echo "<script>alert('Harap isi semua kolom wajib!'); window.history.back();</script>";
        exit();
    }

    try {

        // ini proses menamnbahkan jadwal keberangkatan 
        $queryInsert = "INSERT INTO keberangkatan 
                        (paket_id, tanggal_berangkat, tgl_kepulangan, maskapai, embarkasi, kuota_penerbangan, keterangan) 
                        VALUES 
                        (:paket_id, :tanggal_berangkat, :tgl_kepulangan, :maskapai, :embarkasi, :kuota_penerbangan, :keterangan)";

        $stmt = $db->prepare($queryInsert);
        $stmt->execute([
            ':paket_id' => $paket_id,
            ':tanggal_berangkat' => $tanggal_berangkat,
            ':tgl_kepulangan' => $tgl_kepulangan,
            ':maskapai' => $maskapai,
            ':embarkasi' => $embarkasi,
            ':kuota_penerbangan' => $kuota_penerbangan,
            ':keterangan' => $keterangan
        ]);
        //untuk merubah/menambahkan id keberangkatan di tabel pendaftaran
        $query = "UPDATE pendaftaran 
              SET keberangkatan_id = :keberangkatan_id,  
                  status = 'Berangkat' 
              WHERE id = :id";

        $stmt = $db->prepare($query);

        // lastInsertId untuk mengambilkan id dari data yang berusan di tambahkan
        $stmt->bindParam(':keberangkatan_id', $db->lastInsertId(), PDO::PARAM_INT);
        $stmt->bindParam(':id', $pendaftaran_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            header("Location: tabel_pendaftaran.php?status=updated");
            exit();
        } else {
            echo "<script>alert('Gagal memperbarui data pendaftaran!'); window.history.back();</script>";
            exit();
        }



        echo "<script>alert('Jadwal keberangkatan berhasil ditambahkan!'); window.location='tabel_keberangkatan.php';</script>";
        exit();
    } catch (PDOException $e) {
        echo "Gagal menambahkan data: " . $e->getMessage();
    }
} else {
    header("Location: form_tambah_jadwal.php");
    exit();


}