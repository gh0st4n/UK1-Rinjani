
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jamaah_id        = intval($_POST['jamaah_id'] ?? 0);
    $paket_id         = intval($_POST['paket_id'] ?? 0);
    $keberangkatan_id = !empty($_POST['keberangkatan_id']) ? intval($_POST['keberangkatan_id']) : null;
    $tgl_daftar       = htmlspecialchars(trim($_POST['tgl_daftar'] ?? date('Y-m-d')));
    $status           = htmlspecialchars(trim($_POST['status'] ?? 'Menunggu'));

    if ($jamaah_id <= 0 || $paket_id <= 0) {
        echo "<script>alert('Pilih Jamaah dan Paket yang valid!'); window.history.back();</script>";
        exit();
    }

    try {
        $db->beginTransaction();

        // 1. Ambil harga paket untuk nominal pembayaran
        $stmtHarga = $db->prepare("SELECT harga FROM paket WHERE id = :paket_id");
        $stmtHarga->execute([':paket_id' => $paket_id]);
        $paketData = $stmtHarga->fetch(PDO::FETCH_ASSOC);
        $nominalHarga = $paketData['harga'] ?? 0;

        // 2. Simpan ke tabel pendaftaran
        $queryPendaftaran = "INSERT INTO pendaftaran (jamaah_id, paket_id, keberangkatan_id, tgl_daftar, status) 
                             VALUES (:jamaah_id, :paket_id, :keberangkatan_id, :tgl_daftar, :status)";
        $stmtP = $db->prepare($queryPendaftaran);
        $stmtP->execute([
            ':jamaah_id'        => $jamaah_id,
            ':paket_id'         => $paket_id,
            ':keberangkatan_id' => $keberangkatan_id,
            ':tgl_daftar'       => $tgl_daftar,
            ':status'           => $status
        ]);
        
        $pendaftaran_id = $db->lastInsertId();

        // 3. Otomatis buat data tagihan di tabel pembayaran (Bukti Kosong)
        $queryPembayaran = "INSERT INTO pembayaran (pendaftaran_id, tgl_bayar, jumlah_bayar, bukti_bayar, status) 
                            VALUES (:pendaftaran_id, NULL, :jumlah_bayar, NULL, 'Menunggu')";
        $stmtBayar = $db->prepare($queryPembayaran);
        $stmtBayar->execute([
            ':pendaftaran_id' => $pendaftaran_id,
            ':jumlah_bayar'   => $nominalHarga
        ]);

        $db->commit();
        header("Location: tabel_pendaftaran.php?status=success");
        exit();

    } catch (PDOException $e) {
        $db->rollBack();
        echo "<script>alert('Gagal menyimpan pendaftaran: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
}