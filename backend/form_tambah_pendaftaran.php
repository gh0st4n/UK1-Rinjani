<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$errorMessage = "";
$successMessage = "";

// Ambil data Jamaah
$stmtJamaah = $db->query("SELECT id, nama_lengkap, nik FROM jamaah ORDER BY nama_lengkap ASC");
$jamaahList = $stmtJamaah->fetchAll(PDO::FETCH_ASSOC);

// Ambil data Paket
$stmtPaket = $db->query("SELECT id, nama_paket, jenis, harga FROM paket ORDER BY nama_paket ASC");
$paketList = $stmtPaket->fetchAll(PDO::FETCH_ASSOC);

// Ambil data Keberangkatan
$stmtKeberangkatan = $db->query("SELECT id, tanggal_berangkat, keterangan FROM keberangkatan ORDER BY tanggal_berangkat ASC");
$keberangkatanList = $stmtKeberangkatan->fetchAll(PDO::FETCH_ASSOC);

// Proses Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jamaah_id        = htmlspecialchars(trim($_POST['jamaah_id'] ?? ''));
    $paket_id         = htmlspecialchars(trim($_POST['paket_id'] ?? ''));
    $keberangkatan_id = !empty($_POST['keberangkatan_id']) ? $_POST['keberangkatan_id'] : null;
    $tgl_daftar       = htmlspecialchars(trim($_POST['tgl_daftar'] ?? date('Y-m-d')));
    $status           = htmlspecialchars(trim($_POST['status'] ?? 'Menunggu'));

    if (empty($jamaah_id) || empty($paket_id)) {
        $errorMessage = "Jamaah dan Paket Wajib dipilih!";
    } else {
        try {
            $sql = "INSERT INTO pendaftaran (jamaah_id, paket_id, keberangkatan_id, tgl_daftar, status) 
                    VALUES (:jamaah_id, :paket_id, :keberangkatan_id, :tgl_daftar, :status)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':jamaah_id'        => $jamaah_id,
                ':paket_id'         => $paket_id,
                ':keberangkatan_id' => $keberangkatan_id,
                ':tgl_daftar'       => $tgl_daftar,
                ':status'           => $status
            ]);

            header("Location: tabel_pendaftaran.php?msg=success");
            exit;
        } catch (PDOException $e) {
            $errorMessage = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}

include "components/header.php";
include "components/sidebar.php";
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-emerald: #064e3b;
        --secondary-emerald: #047857;
        --accent-gold: #d97706;
        --bg-modern: #f8fafc;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: var(--bg-modern);
    }

    .icon-header-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }

    .form-card {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        width: 100%;
    }

    .form-label {
        font-size: 0.825rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control,
    .form-select {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 0.75rem 1rem;
        font-size: 0.925rem;
        transition: all 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--secondary-emerald);
        box-shadow: 0 0 0 4px rgba(4, 120, 87, 0.1);
    }

    .btn-gold {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-gold:hover {
        opacity: 0.95;
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(217, 119, 6, 0.3);
    }

    .btn-cancel {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-cancel:hover {
        background-color: #e2e8f0;
        color: #1e293b;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Judul -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Tambah Pendaftaran</h3>
                    <p class="mb-0 text-muted small">Input transaksi pendaftaran porsi Haji dan Umroh baru</p>
                </div>
            </div>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Form Card Container Full Width -->
        <div class="row">
            <div class="col-12">
                <div class="card form-card border-0 shadow-sm bg-white">
                    <div class="card-body p-4 p-md-5">
                        <h5 class="fw-bold text-dark mb-1">Formulir Transaksi</h5>
                        <p class="text-muted small mb-4">Lengkapi data jamaah, pilihan paket, dan status pendaftaran</p>

                        <form action="" method="POST">
                            <div class="row g-3">
                                <!-- Pilih Jamaah -->
                                <div class="col-md-6 mb-2">
                                    <label for="jamaah_id" class="form-label">Jamaah Terdaftar <span class="text-danger">*</span></label>
                                    <select name="jamaah_id" id="jamaah_id" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Jamaah --</option>
                                        <?php foreach ($jamaahList as $j): ?>
                                            <option value="<?= $j['id']; ?>">
                                                <?= htmlspecialchars($j['nama_lengkap']); ?> (NIK: <?= htmlspecialchars($j['nik']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Pilih Paket -->
                                <div class="col-md-6 mb-2">
                                    <label for="paket_id" class="form-label">Paket Layanan <span class="text-danger">*</span></label>
                                    <select name="paket_id" id="paket_id" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Paket Haji / Umroh --</option>
                                        <?php foreach ($paketList as $pk): ?>
                                            <option value="<?= $pk['id']; ?>">
                                                <?= htmlspecialchars($pk['nama_paket']); ?> - [<?= htmlspecialchars($pk['jenis']); ?>] - Rp <?= number_format($pk['harga'], 0, ',', '.'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Jadwal Keberangkatan -->
                                <div class="col-md-4 mb-2">
                                    <label for="keberangkatan_id" class="form-label">Jadwal Keberangkatan</label>
                                    <select name="keberangkatan_id" id="keberangkatan_id" class="form-select">
                                        <option value="">-- Belum Dijadwalkan --</option>
                                        <?php foreach ($keberangkatanList as $kb): ?>
                                            <option value="<?= $kb['id']; ?>">
                                                <?= date('d M Y', strtotime($kb['tanggal_berangkat'])); ?>
                                                <?= !empty($kb['keterangan']) ? '(' . htmlspecialchars($kb['keterangan']) . ')' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Tanggal Daftar -->
                                <div class="col-md-4 mb-2">
                                    <label for="tgl_daftar" class="form-label">Tanggal Pendaftaran <span class="text-danger">*</span></label>
                                    <input type="date" name="tgl_daftar" id="tgl_daftar" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                </div>

                                <!-- Status Pendaftaran -->
                                <div class="col-md-4 mb-2">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="Menunggu" selected>Menunggu</option>
                                        <option value="Berangkat">Berangkat</option>
                                        <option value="Proses">Proses</option>
                                        <option value="Pulang">Pulang</option>
                                        <option value="Selesai">Selesai</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4" style="border-color: #f1f5f9;">

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="tabel_pendaftaran.php" class="btn btn-cancel px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                                    <i class="fas fa-arrow-left"></i>Kembali</a>
                                <button type="submit" class="btn btn-gold px-4 py-2.5 shadow-sm">
                                    <i class="fas fa-save me-1"></i> Simpan Pendaftaran
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include "components/footer.php";
include "components/bottom.php";
?>