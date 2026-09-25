
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id               = $_POST['id'] ?? null;
    $paket_id         = $_POST['paket_id'] ?? null;
    $tanggal_berangkat= $_POST['tanggal_berangkat'] ?? null;
    $tgl_kepulangan   = !empty($_POST['tgl_kepulangan']) ? $_POST['tgl_kepulangan'] : null;
    $maskapai         = htmlspecialchars(trim($_POST['maskapai'] ?? ''));
    $embarkasi        = htmlspecialchars(trim($_POST['embarkasi'] ?? ''));
    $kuota            = $_POST['kuota'] ?? 0;
    $keterangan       = htmlspecialchars(trim($_POST['keterangan'] ?? ''));

    // Validasi data input
    if (!$id || !$paket_id || !$tanggal_berangkat || empty($maskapai) || empty($embarkasi)) {
        echo "<script>
                alert('Mohon lengkapi semua kolom yang wajib diisi!');
                window.history.back();
              </script>";
        exit();
    }

    try {
        // Cek struktur tabel yang digunakan (keberangkatan ATAU jadwal_keberangkatan)
        $targetTable = "keberangkatan";
        
        $checkTable = $db->query("SHOW TABLES LIKE 'keberangkatan'");
        if ($checkTable->rowCount() == 0) {
            $targetTable = "jadwal_keberangkatan";
        }

        // Cek nama kolom tanggal di tabel
        $columnsStmt = $db->query("SHOW COLUMNS FROM `$targetTable`");
        $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);

        $tglBerangkatCol = in_array('tanggal_berangkat', $columns) ? 'tanggal_berangkat' : (in_array('tgl_keberangkatan', $columns) ? 'tgl_keberangkatan' : 'tanggal');
        $kuotaCol        = in_array('kuota_penerbangan', $columns) ? 'kuota_penerbangan' : 'kuota';

        // Query UPDATE
        $query = "UPDATE `$targetTable` SET 
                    paket_id = :paket_id,
                    `$tglBerangkatCol` = :tanggal_berangkat,
                    tgl_kepulangan = :tgl_kepulangan,
                    maskapai = :maskapai,
                    embarkasi = :embarkasi,
                    `$kuotaCol` = :kuota,
                    keterangan = :keterangan
                  WHERE id = :id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':paket_id', $paket_id);
        $stmt->bindParam(':tanggal_berangkat', $tanggal_berangkat);
        $stmt->bindParam(':tgl_kepulangan', $tgl_kepulangan);
        $stmt->bindParam(':maskapai', $maskapai);
        $stmt->bindParam(':embarkasi', $embarkasi);
        $stmt->bindParam(':kuota', $kuota);
        $stmt->bindParam(':keterangan', $keterangan);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Jadwal keberangkatan berhasil diperbarui!');
                    window.location.href = 'tabel_keberangkatan.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal memperbarui jadwal keberangkatan.');
                    window.history.back();
                  </script>";
        }

    } catch (PDOException $e) {
        echo "<script>
                alert('Terjadi kesalahan database: " . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
    }
} else {
    header("Location: tabel_keberangkatan.php");
    exit();
}