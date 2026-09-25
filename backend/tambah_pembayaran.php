
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$errorMessage = "";
$successMessage = "";

// Ambil data Pendaftaran beserta nama Jamaah dan Paket untuk dropdown
$queryPendaftaran = "SELECT p.id, j.nama_lengkap, j.nik, pk.nama_paket 
                    FROM pendaftaran p 
                    JOIN jamaah j ON p.jamaah_id = j.id 
                    JOIN paket pk ON p.paket_id = pk.id 
                    ORDER BY j.nama_lengkap ASC";
$stmtPendaftaran = $db->query($queryPendaftaran);
$pendaftaranList = $stmtPendaftaran->fetchAll(PDO::FETCH_ASSOC);

// Proses Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pendaftaran_id = ($_POST['pendaftaran_id'] ?? '');
    $tanggal_bayar  = ($_POST['tanggal_bayar'] ?? date('Y-m-d'));
    $nominal        = htmlspecialchars($_POST['nominal'] ?? '');
    $status         = htmlspecialchars($_POST['status'] ?? 'Valid');
    
    $bukti_transfer = '';

    if (empty($pendaftaran_id) || empty($nominal) || empty($tanggal_bayar)) {
        $errorMessage = "Jamaah/Pendaftaran, Nominal, dan Tanggal Bayar wajib diisi!";
    } else {
        // Upload Bukti Transfer jika ada file yang diunggah
        if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['bukti_transfer']['tmp_name'];
            $fileName = $_FILES['bukti_transfer']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = 'tf_' . time() . '_' . rand(100, 999) . '.' . $fileExtension;
                $uploadFileDir = 'uploads/bukti_transfer/';

                // Buat direktori jika belum ada
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }

                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $bukti_transfer = $newFileName;
                } else {
                    $errorMessage = "Gagal mengunggah bukti transfer.";
                }
            } else {
                $errorMessage = "Format bukti transfer harus JPG, PNG, atau PDF!";
            }
        }

        if (empty($errorMessage)) {
            try {
                $sql = "INSERT INTO pembayaran (pendaftaran_id, tanggal_bayar, nominal, bukti_transfer, status) 
                        VALUES (:pendaftaran_id, :tanggal_bayar, :nominal, :bukti_transfer, :status)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':pendaftaran_id' => $pendaftaran_id,
                    ':tanggal_bayar'  => $tanggal_bayar,
                    ':nominal'        => $nominal,
                    ':bukti_transfer' => $bukti_transfer,
                    ':status'         => $status
                ]);

                header("Location: tabel_pembayaran.php?msg=success");
                exit;
            } catch (PDOException $e) {
                $errorMessage = "Gagal menyimpan data transaksi: " . $e->getMessage();
            }
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

    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 0.75rem 1rem;
        font-size: 0.925rem;
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
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
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Catat Pembayaran</h3>
                    <p class="mb-0 text-muted small">Input transaksi angsuran dan pelunasan porsi Haji & Umroh</p>
                </div>
            </div>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Form Card Container -->
        <div class="row">
            <div class="col-12">
                <div class="card form-card border-0 shadow-sm bg-white">
                    <div class="card-body p-4 p-md-5">
                        <h5 class="fw-bold text-dark mb-1">Formulir Pembayaran</h5>
                        <p class="text-muted small mb-4">Isi data transaksi cicilan atau pelunasan tagihan jamaah</p>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row g-3">
                                <!-- Pilih Pendaftaran / Jamaah -->
                                <div class="col-md-6 mb-2">
                                    <label for="pendaftaran_id" class="form-label">Jamaah / Transaksi <span class="text-danger">*</span></label>
                                    <select name="pendaftaran_id" id="pendaftaran_id" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Jamaah & Paket --</option>
                                        <?php foreach ($pendaftaranList as $p): ?>
                                            <option value="<?= $p['id']; ?>">
                                                <?= htmlspecialchars($p['nama_lengkap']); ?> (NIK: <?= htmlspecialchars($p['nik']); ?>) - Paket: <?= htmlspecialchars($p['nama_paket']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Nominal Bayar -->
                                <div class="col-md-6 mb-2">
                                    <label for="nominal" class="form-label">Nominal Pembayaran (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" name="nominal" id="nominal" class="form-control" placeholder="Contoh: 5000000" min="0" step="1000" required>
                                </div>

                                <!-- Tanggal Bayar -->
                                <div class="col-md-4 mb-2">
                                    <label for="tanggal_bayar" class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_bayar" id="tanggal_bayar" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                </div>

                                <!-- Upload Bukti Transfer -->
                                <div class="col-md-4 mb-2">
                                    <label for="bukti_transfer" class="form-label">Bukti Transfer (Opsional)</label>
                                    <input type="file" name="bukti_transfer" id="bukti_transfer" class="form-control" accept="image/*,.pdf">
                                </div>

                                <!-- Status Verifikasi Pembayaran -->
                                <div class="col-md-4 mb-2">
                                    <label for="status" class="form-label">Status Verifikasi <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="Valid" selected>Valid / Diterima</option>
                                        <option value="Pending">Pending / Diperiksa</option>
                                        <option value="Tidak Valid">Tidak Valid / Ditolak</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4" style="border-color: #f1f5f9;">

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="tabel_pembayaran.php" class="btn btn-cancel px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-gold px-4 py-2.5 shadow-sm">
                                    <i class="fas fa-save me-1"></i> Simpan Pembayaran
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