
<!-- prosses_update_pembayaran.php -->
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas', 'jamaah']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pembayaranId = $_POST['pembayaran_id'] ?? null;
    $pendaftaranId = $_POST['pendaftaran_id'] ?? null;
    $sisaPembayaran = htmlspecialchars(trim($_POST['sisa_pembayaran'] ?? 0));
    $statusInput = htmlspecialchars(trim($_POST['status'] ?? 'Pending'));

    // Pemetaan status dari form ke Enum Database (Huruf Kapital di Awal)
    $statusPembayaran = 'Pending';
    if ($statusInput === 'Lunas' || $statusInput === 'Cicil') { 
        $statusPembayaran = 'Valid';
    } else if ($statusInput === 'Ditolak') {
        $statusPembayaran = 'Ditolak';
    }

    // die($statusPembayaran);

    try {
        $db->beginTransaction();

        // Cari transaksi pembayaran jika pembayaran_id belum ada tetapi pendaftaran_id ada
        if (empty($pembayaranId) && !empty($pendaftaranId)) {
            $qCek = "SELECT pm.id FROM pembayaran pm 
                                     JOIN pendaftaran p ON pm.jamaah_id = p.jamaah_id 
                                     WHERE p.id = :pendaftaran_id 
                                     ORDER BY pm.id DESC LIMIT 1";
            $stmtCek = $db->prepare($qCek);
            
            $stmtCek->execute([':pendaftaran_id' => $pendaftaranId]);

          
            $existPembayaran = $stmtCek->fetch(PDO::FETCH_ASSOC);
            if ($existPembayaran) {
                $pembayaranId = $existPembayaran['id'];
            }
        }

        // 1. Update jika data pembayaran ditemukan
        if (!empty($pembayaranId)) {
            $queryUpdate = "UPDATE pembayaran SET sisa_pembayaran = :sisa, status =:status WHERE id = :id";

            //   die($statusPembayaran);
            $stmt = $db->prepare($queryUpdate);
            $stmt->execute([
                ':sisa' => $sisaPembayaran,
                ':status' => $statusPembayaran,
                ':id' => $pembayaranId
            ]);
            
        } else if (!empty($pendaftaranId)) {
            // Jika belum ada record pembayaran sama sekali, buat baru (INSERT)
            $stmtGetPendaftaran = $db->prepare("SELECT jamaah_id, keberangkatan_id, paket_id FROM pendaftaran WHERE id = :pendaftaran_id LIMIT 1");
            $stmtGetPendaftaran->execute([':pendaftaran_id' => $pendaftaranId]);
            $dataPendaftaran = $stmtGetPendaftaran->fetch(PDO::FETCH_ASSOC);

            if ($dataPendaftaran && isset($dataPendaftaran['jamaah_id'])) {
                $keberangkatanId = $dataPendaftaran['keberangkatan_id'];

                if (empty($keberangkatanId) && !empty($dataPendaftaran['paket_id'])) {
                    $stmtGetKeberangkatan = $db->prepare("SELECT id FROM keberangkatan WHERE paket_id = :paket_id LIMIT 1");
                    $stmtGetKeberangkatan->execute([':paket_id' => $dataPendaftaran['paket_id']]);
                    $dataKeberangkatan = $stmtGetKeberangkatan->fetch(PDO::FETCH_ASSOC);
                    
                    if ($dataKeberangkatan) {
                        $keberangkatanId = $dataKeberangkatan['id'];
                    }
                }

                if (empty($keberangkatanId)) {
                    throw new Exception("Data keberangkatan untuk pendaftaran ini belum ditentukan.");
                }

                $queryInsert = "INSERT INTO pembayaran (jamaah_id, keberangkatan_id, nominal, sisa_pembayaran, status, tanggal_bayar) 
                                VALUES (:jamaah_id, :keberangkatan_id, 0, :sisa, :status, NOW())";
                // die("INSERT INTO pembayaran (jamaah_id, keberangkatan_id, nominal, sisa_pembayaran, status, tanggal_bayar) VALUES (".$dataPendaftaran['jamaah_id'].", $keberangkatanId, 0, $sisaPembayaran, $statusPembayaran, NOW())");
            
                $stmt = $db->prepare($queryInsert);
                $stmt->execute([
                    ':jamaah_id' => $dataPendaftaran['jamaah_id'],
                    ':keberangkatan_id' => $keberangkatanId,
                    ':sisa' => $sisaPembayaran,
                    ':status' => $statusPembayaran
                ]);
            }
        }
        // die($statusInput);
        // 2. Jika Pembayaran Full / Lunas / Sisa = 0, perbarui status pendaftaran jamaah
        if ($statusInput === 'Lunas' || (float)$sisaPembayaran == 0) {
            // die('lunas');
            if (!empty($pendaftaranId)) {
                $queryPendaftaran = "UPDATE pendaftaran SET status='Proses' WHERE id =:pendaftaran_id";
                $stmtPendaftaran = $db->prepare($queryPendaftaran);
                // die($stmtPendaftaran);
                $stmtPendaftaran->execute([':pendaftaran_id' => $pendaftaranId]);
            }
        }
        
        $db->commit();
    
        header("Location: tabel_pembayaran.php?status=success");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        die("Gagal memperbarui data: " . $e);
    }
} else {
    header("Location: tabel_pembayaran.php");
    exit;

}