
<!-- riwayat_pendaftaran.php -->
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

$auth->checkRole(['jamaah']);

$userRole = $_SESSION['role'] ?? '';
$userId   = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$jamaahId = $_SESSION['jamaah_id'] ?? null;

$riwayat = [];
$errorMessage = "";
$successMessage = "";

if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $successMessage = "Pendaftaran berhasil diajukan! Silakan tunggu verifikasi admin.";
}

try {
    $query = "SELECT p.*, 
                     COALESCE(j.nama_lengkap, 'Jamaah') AS nama_jamaah, 
                     j.no_hp,
                     pk.nama_paket,
                     COALESCE(pk.harga, 0) AS harga_paket,
                     COALESCE(pk.jenis, pk.tipe, 'Haji/Umroh') AS jenis_layanan,
                     pk.durasi,
                     pk.deskripsi
              FROM pendaftaran p
              LEFT JOIN jamaah j ON p.jamaah_id = j.id
              LEFT JOIN paket pk ON p.paket_id = pk.id";

    $params = [];

    if (!empty($jamaahId)) {
        $query .= " WHERE p.jamaah_id = :jamaah_id";
        $params[':jamaah_id'] = $jamaahId;
    } elseif (!empty($userId)) {
        $query .= " WHERE j.user_id = :user_id";
        $params[':user_id'] = $userId;
    }

    $query .= " ORDER BY p.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    try {
        $queryFallback = "SELECT p.*, 
                                 'Jamaah' AS nama_jamaah,
                                 pk.nama_paket,
                                 COALESCE(pk.harga, 0) AS harga_paket,
                                 'Haji/Umroh' AS jenis_layanan,
                                 pk.durasi,
                                 pb.status as status_pembayaran,
                                 pk.deskripsi
                          FROM pendaftaran p
                          LEFT JOIN pembayaran pb ON p.id = pb.pendaftaran_id
                          LEFT JOIN jamaah j ON p.jamaah_id = j.id
                          LEFT JOIN paket pk ON p.paket_id = pk.id";

        $paramsFallback = [];
        if (!empty($jamaahId)) {
            $queryFallback .= " WHERE p.jamaah_id = :jamaah_id";
            $paramsFallback[':jamaah_id'] = $jamaahId;
        } elseif (!empty($userId)) {
            $queryFallback .= " WHERE j.user_id = :user_id";
            $paramsFallback[':user_id'] = $userId;
        }

        $queryFallback .= " ORDER BY p.id DESC";

        $stmt = $db->prepare($queryFallback);
        $stmt->execute($paramsFallback);
        $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $errorMessage = "Terjadi kesalahan saat mengambil riwayat pendaftaran: " . $ex->getMessage();
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

    .table-card {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        width: 100%;
    }

    .table th {
        font-size: 0.825rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%);
        color: #ffffff;
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-gold:hover {
        background: linear-gradient(135deg, #c29d26 0%, #996515 100%);
        color: #ffffff;
    }

    /* Style Tombol Aksi */
    .btn-action-yellow {
        background-color: #fef3c7;
        color: #d97706;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-action-yellow:hover {
        background-color: #fde68a;
        color: #b45309;
    }

    .btn-action-blue {
        background-color: #e0f2fe;
        color: #0284c7;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-action-blue:hover {
        background-color: #bae6fd;
        color: #0369a1;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Pendaftaran </h3>
                    <p class="mb-0 text-muted small">Pantau status pendaftaran perjalanan ibadah Haji dan Umroh Anda</p>
                </div>
            </div>

            <!-- Tombol Mengarah ke Halaman Baru pilih_layanan.php -->
\
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Table Card Container -->
        <div class="card table-card border-0 shadow-sm bg-white">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Riwayat Pendaftaran</h5>
                        <p class="text-muted small mb-0">Informasi detail status verifikasi, layanan, dan pembayaran Anda</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                        <?= count($riwayat); ?> Pendaftaran
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="border-bottom">
                                <th class="pb-3 ps-2" style="width: 50px;">NO</th>
                                <th class="pb-3">PROGRAM & LAYANAN</th>
                                <th class="pb-3">TGL DAFTAR</th>
                                <th class="pb-3 text-end">TOTAL BIAYA</th>
                                <th class="pb-3 text-center">STATUS</th>
                                <th class="pb-3 text-center" style="width: 100px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($riwayat) > 0): ?>
                                <?php $no = 1; foreach ($riwayat as $row): ?>
                                <?php 
                                    $tglDaftarVal = $row['tgl_daftar'] ?? $row['tanggal_daftar'] ?? $row['created_at'] ?? null;
                                    
                                    // $status = strtolower($row['status_pembayaran'] ?? 'Pending');
                                    $status = strtolower($row['status_pembayaran'] ?? 'Pending');
                                    // echo($status);
                                    
                                    if ($status === 'Lunas' || $status === 'Verified' || $status === 'Disetujui' || $status === 'Valid') {
                                        $statusBadge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #d1fae5; color: #047857; font-size: 11px;"><i class="fas fa-check-circle me-1"></i> Lunas</span>';
                                    } elseif ($status === 'Dp' || $status === 'Sebagian') {
                                        $statusBadge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #fef3c7; color: #b45309; font-size: 11px;"><i class="fas fa-wallet me-1"></i> DP / Cicil</span>';
                                    } elseif ($status === 'Batal' || $status === 'Dibatalkan') {
                                        $statusBadge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #fee2e2; color: #ef4444; font-size: 11px;"><i class="fas fa-times-circle me-1"></i> Dibatalkan</span>';
                                    } else {
                                        $statusBadge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #e0f2fe; color: #0284c7; font-size: 11px;"><i class="fas fa-clock me-1"></i> Pending</span>';
                                    }

                                    // Perbaikan nilai Rp 0: prioritaskan nominal pendaftaran, jika kosong gunakan harga dari tabel paket
                                    $totalBiaya = !empty($row['total_biaya']) ? $row['total_biaya'] : ($row['harga_paket'] ?? 0);
                                ?>
                                <tr class="border-bottom">
                                    <td class="ps-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 fw-bold text-muted" 
                                             style="width: 32px; height: 32px; background-color: #f1f5f9; font-size: 13px;">
                                            <?= $no++; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 14px;"><?= htmlspecialchars($row['nama_paket'] ?? 'Layanan Belum Dipilih'); ?></div>
                                        <div class="text-muted small" style="font-size: 12px;">
                                            <span class="badge px-2 py-1 mt-1" style="background-color: #f1f5f9; color: #475569;">
                                                <?= htmlspecialchars($row['jenis_layanan']); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="fw-semibold text-secondary" style="font-size: 13px;">
                                        <i class="far fa-calendar-alt me-1 text-muted"></i>
                                        <?= $tglDaftarVal ? date('d M Y', strtotime($tglDaftarVal)) : '-'; ?>
                                    </td>

                                    <td class="text-end fw-bold" style="color: var(--secondary-emerald); font-size: 13px;">
                                        Rp <?= number_format($totalBiaya, 0, ',', '.'); ?>
                                    </td>

                                    <td class="text-center">
                                        <?= $statusBadge; ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <!-- Tombol Detail / Lihat (Mata Kuning) Membuka Modal -->
                                            <button type="button" class="btn-action-yellow" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id']; ?>" title="Lihat Detail">
                                                <i class="fas fa-eye" style="font-size: 12px;"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Detail Paket Travel -->
                                <div class="modal fade" id="modalDetail<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4 shadow p-3">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <span class="d-inline-flex align-items-center justify-content-center bg-warning text-white rounded-circle" style="width: 28px; height: 28px; font-size: 14px;">
                                                        <i class="fas fa-info"></i>
                                                    </span>
                                                    Detail Paket Travel
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body pt-4">
                                                <div class="row g-3 mb-4">
                                                    <div class="col-6">
                                                        <p class="text-muted small mb-1">Nama Paket</p>
                                                        <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($row['nama_paket'] ?? '-'); ?></h6>
                                                    </div>
                                                    <div class="col-6">
                                                        <p class="text-muted small mb-1">Jenis Paket</p>
                                                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-semibold">
                                                            <?= htmlspecialchars($row['jenis_layanan']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="col-6">
                                                        <p class="text-muted small mb-1">Harga / Pax</p>
                                                        <h5 class="fw-bold text-success mb-0">Rp <?= number_format($totalBiaya, 0, ',', '.'); ?></h5>
                                                    </div>
                                                    <div class="col-6">
                                                        <p class="text-muted small mb-1">Durasi</p>
                                                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                                            <i class="far fa-clock text-muted"></i> 
                                                            <?= !empty($row['durasi']) ? htmlspecialchars($row['durasi']) . ' Hari' : '15 Hari'; ?>
                                                        </h6>
                                                    </div>
                                                </div>

                                                <div>
                                                    <p class="text-muted small mb-2">Deskripsi & Fasilitas Paket</p>
                                                    <div class="p-3 bg-light rounded-3 text-secondary small" style="min-height: 80px;">
                                                        <?= nl2br(htmlspecialchars($row['deskripsi'] ?? "Hotel Premium, Transportasi Eksklusif, Konsumsi, Pembimbing")); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-secondary px-4 py-2 rounded-3 fw-semibold" data-bs-dismiss="modal" style="background-color: #64748b; border: none;">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-2x mb-3 d-block opacity-25"></i>
                                        Anda belum memiliki riwayat pendaftaran.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include "components/footer.php";
include "components/bottom.php"; 
?>