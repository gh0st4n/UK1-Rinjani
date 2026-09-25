<!-- tabel_pendaftaran -->
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$userRole = $_SESSION['role'] ?? '';

$pendaftaran = [];
$errorMessage = "";

try {
    $query = "SELECT p.*, 
                     j.nama_lengkap, 
                     j.nik,
                     pk.nama_paket, 
                     pk.jenis,
                     kb.tanggal_berangkat 
              FROM pendaftaran p 
              JOIN jamaah j ON p.jamaah_id = j.id 
              JOIN paket pk ON p.paket_id = pk.id 
              LEFT JOIN keberangkatan kb ON p.keberangkatan_id = kb.id 
              ORDER BY p.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $pendaftaran = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Terjadi kesalahan saat mengambil data: " . $e->getMessage();
}

// Hitung statistik pendaftaran & keberangkatan
$totalPendaftaran = count($pendaftaran);
$sudahDijadwalkan = 0;
$belumDijadwalkan = 0;

foreach ($pendaftaran as $p) {
    if (!empty($p['tanggal_berangkat'])) {
        $sudahDijadwalkan++;
    } else {
        $belumDijadwalkan++;
    }
}



include "components/header.php";
include "components/sidebar.php";
?>

<!-- Import Google Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">

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

    .stat-card {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
    }

    .table-card {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }

    .table th {
        color: #475569;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        border-bottom: 2px solid #f1f5f9 !important;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .action-btn-edit {
        background-color: #f0fdf4;
        color: var(--secondary-emerald);
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .action-btn-edit:hover {
        background-color: var(--secondary-emerald);
        color: #ffffff;
    }

    .action-btn-delete {
        background-color: #fef2f2;
        color: #ef4444;
        border: 1px solid #fecaca;
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .action-btn-delete:hover {
        background-color: #ef4444;
        color: #ffffff;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Data Pendaftaran</h3>
                    <p class="mb-0 text-muted small">Kelola transaksi pendaftaran porsi Haji dan Umroh</p>
                </div>
            </div>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Stat Cards Summary (Sudah Diperbarui) -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-white border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Total Pendaftaran</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $totalPendaftaran; ?> <span
                                    class="fs-6 fw-normal text-muted">Jamaah</span></h3>
                        </div>
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center"
                            style="background-color: #f0fdf4; color: var(--secondary-emerald); width: 48px; height: 48px;">
                            <i class="fas fa-file-invoice fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-white border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Sudah Dijadwalkan</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $sudahDijadwalkan; ?> <span
                                    class="fs-6 fw-normal text-muted">Jamaah</span></h3>
                        </div>
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center"
                            style="background-color: #e0f2fe; color: #0284c7; width: 48px; height: 48px;">
                            <i class="fas fa-plane-departure fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-white border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Belum Dijadwalkan</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $belumDijadwalkan; ?> <span
                                    class="fs-6 fw-normal text-muted">Jamaah</span></h3>
                        </div>
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center"
                            style="background-color: #fff7ed; color: #ea580c; width: 48px; height: 48px;">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card Container -->
        <div class="card table-card border-0 shadow-sm bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Daftar Transaksi Pendaftaran</h5>
                        <p class="text-muted small mb-0">Data pendaftaran jamaah beserta status porsi terkini</p>
                    </div>
                    <span
                        class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold"><?= $totalPendaftaran; ?>
                        Pendaftaran</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle border-0 mb-0">
                        <thead>
                            <tr>
                                <th class="border-0 pb-3" style="width: 50px;">NO</th>
                                <th class="border-0 pb-3">JAMAAH</th>
                                <th class="border-0 pb-3">PAKET</th>
                                <th class="border-0 pb-3">TGL DAFTAR</th>
                                <th class="border-0 pb-3">KEBERANGKATAN</th>
                                <th class="border-0 pb-3">STATUS</th>
                                <th class="border-0 pb-3 text-center" style="width: 110px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            // biasakan melakukan vardump apabila terjadi data yang masih NULL, 
                            // var_dump($pendaftaran);

                            ?>
                            <?php if (count($pendaftaran) > 0): ?>
                                <?php $no = 1;
                                foreach ($pendaftaran as $row): ?>
                                    <tr class="border-top">
                                        <td class="py-3 fw-bold text-secondary"><?= $no++; ?></td>

                                        <!-- Nama Jamaah & NIK -->
                                        <td class="py-3">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_lengkap']); ?></div>
                                            <div class="text-muted small font-monospace">NIK:
                                                <?= htmlspecialchars($row['nik']); ?></div>
                                        </td>

                                        <!-- Nama Paket -->
                                        <td class="py-3">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_paket']); ?></div>
                                            <span class="badge bg-light text-secondary border mt-1"
                                                style="font-size: 11px;"><?= htmlspecialchars($row['jenis']); ?></span>
                                        </td>

                                        <!-- Tgl Daftar -->
                                        <td class="py-3 text-secondary small fw-medium">
                                            <i class="far fa-calendar-alt me-1 text-muted"></i>
                                            <?= date('d M Y', strtotime($row['tgl_daftar'])); ?>
                                        </td>

                                        <!-- Keberangkatan -->
                                        <td class="py-3 text-secondary small fw-medium">
                                            <?php if (!empty($row['tanggal_berangkat'])): ?>
                                                <i class="fas fa-plane-departure me-1 text-muted"></i>
                                                <?= date('d M Y', strtotime($row['tanggal_berangkat'])); ?>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted fw-normal fst-italic">Belum
                                                    dijadwalkan</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="py-3">
                                            <?php
                                            $st = strtolower($row['status'] ?? 'menunggu');

                                            switch ($st) {
                                                case 'terdaftar':
                                                case 'aktif':
                                                    $bgClass = "background-color: #d1fae5; color: #059669; border: 1px solid #a7f3d0;";
                                                    $iconClass = "fas fa-check-circle";
                                                    break;
                                                case 'berangkat':
                                                    $bgClass = "background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;";
                                                    $iconClass = "fas fa-plane-departure";
                                                    break;
                                                case 'selesai':
                                                case 'pulang':
                                                    $bgClass = "background-color: #f3e8ff; color: #9333ea; border: 1px solid #e9d5ff;";
                                                    $iconClass = "fas fa-plane-arrival";
                                                    break;
                                                case 'menunggu':
                                                default:
                                                    $bgClass = "background-color: #fff7ed; color: #ea580c; border: 1px solid #ffedd5;";
                                                    $iconClass = "fas fa-clock";
                                                    break;
                                            }
                                            ?>
                                            <span class="badge rounded-pill px-3 py-2 fw-semibold" style="<?= $bgClass; ?>">
                                                <i class="<?= $iconClass; ?> me-1"></i> <?= ucfirst($st); ?>
                                            </span>
                                        </td>

                                        <!-- Tombol Aksi -->
                                        <td class="py-3 text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <!-- <a href="form_update_pendaftaran.php?id=<?= $row['id']; ?>" class="btn btn-sm action-btn-edit px-2.5 py-1.5" title="Edit Data">
                                                    <i class="fas fa-edit"></i>
                                                </a> -->
                                                <!-- <a href="delete_pendaftaran.php?id=<?= $row['id']; ?>"
                                                    class="btn btn-sm action-btn-delete px-2.5 py-1.5"
                                                    onclick="return confirm('Yakin ingin menghapus data pendaftaran ini?');"
                                                    title="Hapus Data">
                                                    <i class="fas fa-trash"></i>
                                                </a> -->

                                                <?php if (empty($row['tanggal_berangkat'])): ?>
                                                    <a href="form_tambah_jadwal.php?id_pendaftaran=<?= $row['id']; ?>"
                                                        class="btn btn-sm action-btn-edit px-2.5 py-1.5" title="Buat Jadwal">
                                                        Buat Jadwal
                                                    </a>
                                                <?php else: ?>
                                                    <a href="#"
                                                        class="btn btn-sm action-btn-disabled px-2.5 py-1.5">
                                                        Sudah Terjadwal
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="border-top">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="my-3">
                                            <i class="fas fa-clipboard-check fa-3x text-light mb-3"
                                                style="color: #cbd5e1 !important;"></i>
                                            <p class="mb-0 fw-semibold text-secondary">Belum ada data pendaftaran terdaftar.
                                            </p>
                                        </div>
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