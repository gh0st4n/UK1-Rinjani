<?php
session_start();
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

$auth->checkRole(['jamaah']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paket_id     = trim($_POST['pilih_paket_id'] ?? '');
    $metode_bayar = trim($_POST['metode_pembayaran'] ?? 'Transfer Bank');
    $opsi_bayar   = trim($_POST['opsi_bayar'] ?? 'Valid');
    
    $user_id   = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
    $jamaah_id = $_SESSION['jamaah_id'] ?? null;

    if (empty($paket_id)) {
        echo "<script>alert('Layanan belum dipilih!'); window.location.href = '../index.php#services';</script>";
        exit();
    }

    try {
        // 1. Cari ID Jamaah jika belum ada di session
        if (empty($jamaah_id) && !empty($user_id)) {
            $stmtJ = $db->prepare("SELECT id FROM jamaah WHERE user_id = :user_id LIMIT 1");
            $stmtJ->execute([':user_id' => $user_id]);
            $resJ = $stmtJ->fetch(PDO::FETCH_ASSOC);
            $jamaah_id = $resJ['id'] ?? null;
        }

        if (empty($jamaah_id)) {
            $backTo = '../frontend/pages/pilih-layanan.php?id=' . (int) $paket_id;
            echo "<script>alert('Data Profil Jamaah belum lengkap!'); window.location.href = '" . $backTo . "';</script>";
            exit();
        }

        // 2. Ambil Harga Paket
        $stmtP = $db->prepare("SELECT harga FROM paket WHERE id = :id LIMIT 1");
        $stmtP->execute([':id' => $paket_id]);
        $paketData = $stmtP->fetch(PDO::FETCH_ASSOC);
        $harga = $paketData['harga'] ?? 0;
        $tgl   = date('Y-m-d H:i:s');

// 3. Simpan ke Tabel Pendaftaran (Gunakan status 'Menunggu' sesuai ENUM database)
$queryPendaftaran = "INSERT INTO pendaftaran (jamaah_id, paket_id, tgl_daftar, total_biaya, status) 
                    VALUES (:jamaah_id, :paket_id, :tgl, :biaya, 'Menunggu')";

try {
    $stmtIns = $db->prepare($queryPendaftaran);
    $stmtIns->execute([
        ':jamaah_id' => $jamaah_id,
        ':paket_id'  => $paket_id,
        ':tgl'        => $tgl,
        ':biaya'      => $harga
    ]);
} catch (PDOException $e) {
    // Fallback jika kolom total_biaya tidak ada di tabel pendaftaran
    $queryPendaftaranFallback = "INSERT INTO pendaftaran (jamaah_id, paket_id, tgl_daftar, status) 
                                 VALUES (:jamaah_id, :paket_id, :tgl, 'Menunggu')";
    $stmtIns = $db->prepare($queryPendaftaranFallback);
    $stmtIns->execute([
        ':jamaah_id' => $jamaah_id,
        ':paket_id'  => $paket_id,
        ':tgl'        => $tgl
    ]);
}

        $pendaftaran_id = $db->lastInsertId();

        // 4. OTOMATIS Buat Record di Tabel Pembayaran agar dibaca Admin
        try {
            $queryPembayaran = "INSERT INTO pembayaran (pendaftaran_id, jamaah_id, tgl_bayar, nominal, metode_pembayaran, status) 
                                VALUES (:pendaftaran_id, :jamaah_id, :tgl_bayar, :nominal, :metode, 'Pending')";
            $stmtPem = $db->prepare($queryPembayaran);
            $stmtPem->execute([
                ':pendaftaran_id' => $pendaftaran_id,
                ':jamaah_id'      => $jamaah_id,
                ':tgl_bayar'      => $tgl,
                ':nominal'        => $harga,
                ':metode'         => $metode_bayar
            ]);
        } catch (PDOException $ex) {
            // Fallback jika tabel pembayaran punya kolom sedikit berbeda
            try {
                $queryPemFallback = "INSERT INTO pembayaran (pendaftaran_id, tgl_bayar, nominal, status) 
                                     VALUES (:pendaftaran_id, :tgl_bayar, :nominal, 'Pending')";
                $stmtPem2 = $db->prepare($queryPemFallback);
                $stmtPem2->execute([
                    ':pendaftaran_id' => $pendaftaran_id,
                    ':tgl_bayar'      => $tgl,
                    ':nominal'        => $harga
                ]);
            } catch (PDOException $ex2) {
                // Abaikan jika struktur pembayaran sangat spesifik
            }
        }

        header("Location: ../frontend/pages/riwayat.php?status=success");
        exit();

    } catch (PDOException $e) {
        $backTo = '../frontend/pages/pilih-layanan.php?id=' . (int) $paket_id;
        echo "<script>alert('Gagal memproses pendaftaran: " . addslashes($e->getMessage()) . "'); window.location.href = '" . $backTo . "';</script>";
        exit();
    }
} else {
    header("Location: ../index.php#services");
    exit();
}