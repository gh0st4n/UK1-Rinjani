
<!-- tabel_keberangkatan.php -->
<?php
include "database/connection.php";
require_once "classes/Auth.php";

$userRole = $_SESSION['role'] ?? '';

// Menggunakan koneksi PDO dari class Database di connection.php
$database = new Database();
$db = $database->getConnection();

// Query mengambil data keberangkatan beserta nama paketnya
$queryKeberangkatan = "SELECT 
        keberangkatan.*, 
        paket.nama_paket 
    FROM keberangkatan 
    JOIN paket ON keberangkatan.paket_id = paket.id 
    ORDER BY keberangkatan.tanggal_berangkat ASC";

$stmt = $db->prepare($queryKeberangkatan);
$stmt->execute();

// Ambil semua data keberangkatan ke dalam array
$keberangkatanList = $stmt->fetchAll();

// Hitung total statistik
$totalJadwal = count($keberangkatanList);
$totalMendatang = 0;
$totalSelesai = 0;
$today = strtotime(date('Y-m-d'));

foreach ($keberangkatanList as $k) {
    $tglBerangkat = strtotime($k['tanggal_berangkat'] ?? '');
    if ($tglBerangkat && $tglBerangkat >= $today) {
        $totalMendatang++;
    } else {
        $totalSelesai++;
    }
}

include "components/header.php";
include "components/sidebar.php";
?>

<!-- Import Google Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-emerald: #064e3b;
        --secondary-emerald: #047857;
        --accent-gold: #d97706;
        --light-gold: #fef3c7;
        --bg-modern: #f8fafc;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: var(--bg-modern);
    }

    /* Stat Cards Modern */
    .stat-card-paket {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card-paket:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06) !important;
    }

    .stat-card-paket::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    .stat-card-paket.total::before {
        background: var(--primary-emerald);
    }

    .stat-card-paket.haji::before {
        background: #0284c7;
    }

    .stat-card-paket.umroh::before {
        background: #10b981;
    }

    .icon-box-p {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }

    /* Styling Table Modern */
    .custom-table-card {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }

    .table-modern thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 0.7px;
        text-transform: uppercase;
        border-bottom: 2px solid #e2e8f0;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .table-modern tbody tr {
        transition: all 0.2s ease;
    }

    .table-modern tbody tr:hover {
        background-color: rgba(241, 245, 249, 0.5);
    }

    .btn-gold {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-gold:hover {
        opacity: 0.95;
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(217, 119, 6, 0.3);
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        border: none;
        text-decoration: none;
    }

    .action-btn-view {
        background-color: #fef3c7;
        color: #d97706;
    }

    .action-btn-view:hover {
        background-color: #d97706;
        color: #ffffff;
    }

    /* Tombol Edit - Warna Biru */
    .action-btn-edit {
        background-color: #e0f2fe;
        color: #0284c7;
    }

    .action-btn-edit:hover {
        background-color: #0284c7;
        color: #ffffff;
    }

    .action-btn-delete {
        background-color: #fee2e2;
        color: #ef4444;
    }

    .action-btn-delete:hover {
        background-color: #ef4444;
        color: #ffffff;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman Modern -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-box-p text-white me-3 shadow-sm" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%);">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Jadwal Keberangkatan</h3>
                    <p class="mb-0 text-muted small">Kelola dan pantau seluruh jadwal keberangkatan Jamaah Umroh & Haji.</p>
                </div>
            </div>

            <!-- Tombol Tambah Jadwal (Diakses Admin dan Petugas) -->
            <?php if (in_array($userRole, ['admin', 'petugas'])): ?>
                <a href="form_tambah_jadwal.php" class="btn btn-gold px-4 py-2.5 shadow-sm d-flex align-items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Jadwal
                </a>
            <?php endif; ?>
        </div>

        <!-- Ringkasan Stats Modern -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card-paket total shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Total Jadwal</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $totalJadwal; ?></h3>
                        </div>
                        <div class="icon-box-p text-emerald" style="background-color: rgba(6, 78, 59, 0.1); color: var(--primary-emerald);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card-paket haji shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Mendatang</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $totalMendatang; ?></h3>
                        </div>
                        <div class="icon-box-p bg-info bg-opacity-10 text-info">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card-paket umroh shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Selesai</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $totalSelesai; ?></h3>
                        </div>
                        <div class="icon-box-p bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card Container Modern -->
        <div class="card custom-table-card border-0 shadow-sm bg-white w-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Daftar Keberangkatan</h5>
                        <p class="mb-0 small text-muted">Jadwal resmi keberangkatan dan kepulangan jamaah</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                        <?= $totalJadwal; ?> Total Jadwal
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 60px;">NO</th>
                                <th>NAMA PAKET</th>
                                <th>TANGGAL KEBERANGKATAN</th>
                                <th>TANGGAL KEPULANGAN</th>
                                <th>MASKAPAI</th>
                                <th>EMBARKASI</th>
                                <th class="text-center">KUOTA</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-center" style="width: 130px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($totalJadwal > 0) :
                                $no = 1;
                                foreach ($keberangkatanList as $row) : 
                                    $idJadwal = $row['id'];
                                    $tglBerangkatStr = $row['tanggal_berangkat'] ?? $row['tgl_keberangkatan'] ?? '';
                                    $tglPulangStr = $row['tanggal_pulang'] ?? $row['tgl_kepulangan'] ?? '';
                                    
                                    $tglKeberangkatan = strtotime($tglBerangkatStr);
                                    
                                    // Penentuan status berdasarkan tanggal keberangkatan
                                    if ($tglKeberangkatan && $tglKeberangkatan >= $today) {
                                        $statusText = 'Mendatang';
                                        $statusBadge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 600;"><i class="fas fa-clock me-1"></i> Mendatang</span>';
                                    } else {
                                        $statusText = 'Selesai';
                                        $statusBadge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #d1fae5; color: #047857; font-size: 11px; font-weight: 600;"><i class="fas fa-check-circle me-1"></i> Selesai</span>';
                                    }
                            ?>
                                <tr class="border-bottom">
                                    <td class="ps-3">
                                        <span class="fw-bold text-secondary small">
                                            <?= sprintf("%02d", $no++); ?>
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($row['nama_paket']); ?></span>
                                    </td>
                                    <td class="py-3 text-secondary small">
                                        <i class="far fa-calendar-alt me-1 text-primary"></i>
                                        <?= !empty($tglBerangkatStr) ? date('d M Y', strtotime($tglBerangkatStr)) : '-'; ?>
                                    </td>
                                    <td class="py-3 text-secondary small">
                                        <i class="far fa-calendar-check me-1 text-success"></i>
                                        <?= !empty($tglPulangStr) ? date('d M Y', strtotime($tglPulangStr)) : '-'; ?>
                                    </td>
                                    <td class="py-3 text-secondary small fw-medium"><?= htmlspecialchars($row['maskapai']); ?></td>
                                    <td class="py-3 text-secondary small fw-medium"><?= htmlspecialchars($row['embarkasi']); ?></td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-2 fw-medium">
                                            <i class="fas fa-users me-1 text-muted"></i><?= htmlspecialchars($row['kuota_penerbangan'] ?? $row['kuota'] ?? '0'); ?> Jamaah
                                        </span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <?= $statusBadge; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <!-- Tombol Lihat (Mata) dengan Modal -->
                                            <button class="action-btn action-btn-view" title="Detail Data" data-bs-toggle="modal" data-bs-target="#modalDetailJadwal<?= $idJadwal; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <!-- Tombol Edit mengarah ke form_update_jadwal.php -->
                                            <a href="form_update_jadwal.php?id=<?= $idJadwal; ?>" class="action-btn action-btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- Tombol Hapus mengarah ke proses_delete_jadwal.php (Admin & Petugas) -->
                                            <?php if (in_array($userRole, ['admin', 'petugas'])): ?>
                                                <a href="process/proses_delete_jadwal.php?id=<?= $idJadwal; ?>" class="action-btn action-btn-delete" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Detail Jadwal (Ikon Mata) -->
                                <div class="modal fade" id="modalDetailJadwal<?= $idJadwal; ?>" tabindex="-1" aria-labelledby="modalDetailLabel<?= $idJadwal; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                                            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                                                <h5 class="modal-title fw-bold text-dark" id="modalDetailLabel<?= $idJadwal; ?>">
                                                    <i class="fas fa-info-circle me-2 text-primary"></i>Detail Keberangkatan
                                                </h5>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="bg-light p-3 rounded-3 mb-3">
                                                    <span class="text-muted small d-block mb-1">Nama Paket</span>
                                                    <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($row['nama_paket']); ?></h6>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-6">
                                                        <div class="p-3 border rounded-3">
                                                            <span class="text-muted small d-block mb-1">Keberangkatan</span>
                                                            <strong class="text-dark small"><i class="far fa-calendar-alt text-primary me-1"></i><?= !empty($tglBerangkatStr) ? date('d M Y', strtotime($tglBerangkatStr)) : '-'; ?></strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="p-3 border rounded-3">
                                                            <span class="text-muted small d-block mb-1">Kepulangan</span>
                                                            <strong class="text-dark small"><i class="far fa-calendar-check text-success me-1"></i><?= !empty($tglPulangStr) ? date('d M Y', strtotime($tglPulangStr)) : '-'; ?></strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="p-3 border rounded-3">
                                                            <span class="text-muted small d-block mb-1">Maskapai</span>
                                                            <strong class="text-dark small"><?= htmlspecialchars($row['maskapai']); ?></strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="p-3 border rounded-3">
                                                            <span class="text-muted small d-block mb-1">Embarkasi</span>
                                                            <strong class="text-dark small"><?= htmlspecialchars($row['embarkasi']); ?></strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="p-3 border rounded-3">
                                                            <span class="text-muted small d-block mb-1">Kuota</span>
                                                            <strong class="text-dark small"><?= htmlspecialchars($row['kuota_penerbangan'] ?? $row['kuota'] ?? '0'); ?> Jamaah</strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="p-3 border rounded-3">
                                                            <span class="text-muted small d-block mb-1">Status</span>
                                                            <strong class="text-dark small"><?= $statusText; ?></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                                                <button type="button" class="btn btn-secondary w-100 rounded-3 fw-semibold" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                endforeach;
                            else : 
                            ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fa-3x mb-3 text-light-gray d-block"></i>
                                        Belum ada data jadwal keberangkatan.
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