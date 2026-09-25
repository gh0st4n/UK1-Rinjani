<?php
include "database/connection.php";
require_once "classes/Auth.php";

$conn = mysqli_connect("localhost", "root", "", "travel_haji_umroh");

$totalPendaftaran = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pendaftaran");
$dataPendaftar = mysqli_fetch_assoc($totalPendaftaran);

$totalLunas = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pembayaran WHERE status = 'Valid'  ");
$dataLunas = mysqli_fetch_assoc($totalLunas);

$totalPembayaran = mysqli_query($conn, "SELECT SUM(nominal) AS totalBayar FROM pembayaran");
$dataPembayaran = mysqli_fetch_assoc($totalPembayaran);

$laporan = mysqli_query($conn, "SELECT jamaah.nama_lengkap, paket.nama_paket, pendaftaran.tgl_daftar, paket.harga, pembayaran.nominal, pembayaran.status
FROM pendaftaran JOIN paket ON pendaftaran.paket_id = paket.id JOIN jamaah ON pendaftaran.jamaah_id LEFT JOIN pembayaran ON jamaah.id = pembayaran.jamaah_id");

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

    .table th {
        font-size: 0.825rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-stat {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s ease;
    }

    .card-stat:hover {
        transform: translateY(-3px);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Generate Laporan</h3>
                    <p class="mb-0 text-muted small">Laporan rekapitulasi pendaftaran dan transaksi pembayaran jamaah</p>
                </div>
            </div>
            
            <a href="cetak_laporan.php" target="_blank" class="btn btn-gold px-4 py-2.5 shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-print"></i> Cetak Laporan
            </a>
        </div>


        <!-- Stat Cards Container -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-stat border-0 shadow-sm bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-semibold mb-1">Total Pendaftaran</p>
                            <h3 class="fw-bold text-dark mb-0"><?= ($dataPendaftar)['total']; ?></h3>
                        </div>
                        <div class="stat-icon bg-light text-primary">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-stat border-0 shadow-sm bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-semibold mb-1">Pendaftaran Lunas</p>
                            <h3 class="fw-bold text-success mb-0"><?= ($dataLunas)['total']; ?></h3>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-stat border-0 shadow-sm bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-semibold mb-1">Total Pembayaran Masuk</p>
                            <h3 class="fw-bold text-dark mb-0">Rp <?= ($dataPembayaran)['totalBayar']; ?></h3>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card Container -->
        <div class="card table-card border-0 shadow-sm bg-white">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Daftar Transaksi Jamaah</h5>
                        <p class="text-muted small mb-0">Rekapitulasi data gabungan jamaah, paket, dan status pembayaran</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                       Data Laporan
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="border-bottom">
                                <th class="pb-3 ps-2" style="width: 50px;">NO</th>
                                <th class="pb-3">INFORMASI JAMAAH</th>
                                <th class="pb-3">PAKET TERPILIH</th>
                                <th class="pb-3">TGL DAFTAR</th>
                                <th class="pb-3">TOTAL HARGA</th>
                                <th class="pb-3">TOTAL DIBAYAR</th>
                                <th class="pb-3 text-center">STATUS BAYAR</th>
                                <th class="pb-3 text-center" style="width: 100px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php while ($data = mysqli_fetch_assoc($laporan)) : ?>
                                <tr class="border-bottom">
                                    <td class="ps-2">
                                      <?= $i; ?>
                                    </td>

                                    <td>
                                        <?= $data['nama_lengkap']; ?>
                                    </td>

                                    <td class="fw-semibold text-secondary" style="font-size: 13px;">
                                        <?= $data['nama_paket']; ?>
                                    </td>

                                    <td class="small text-muted" style="font-size: 13px;">
                                        <?= $data['tgl_daftar']; ?>
                                    </td>

                                    <td class="fw-bold text-dark" style="font-size: 13px;">
                                         <?= $data['harga']; ?>
                                    </td>

                                    <td class="fw-bold text-success" style="font-size: 13px;">
                                         <?= $data['nominal']; ?>
                                    </td>

                                    <td class="text-center">
                                        <?= $data['status']; ?>
                                    </td>

                                    <td class="text-center">
                                        <a href="" 
                                           class="btn btn-sm d-inline-flex align-items-center justify-content-center rounded-3 border-0" 
                                           style="background-color: #e0f2fe; color: #0284c7; width: 34px; height: 34px;" 
                                           title="Detail Pembayaran">
                                            <i class="fas fa-eye" style="font-size: 13px;"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            <?php endwhile; ?>
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