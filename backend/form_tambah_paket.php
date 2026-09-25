<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

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
    }

    .form-label {
        color: #334155;
        font-size: 0.85rem;
    }

    .form-control,
    .form-select {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 0.65rem 0.9rem;
        font-size: 0.925rem;
        transition: all 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--secondary-emerald);
        box-shadow: 0 0 0 4px rgba(4, 120, 87, 0.1);
    }

    .input-group-text-emerald {
        background-color: var(--primary-emerald);
        color: #ffffff;
        border: none;
        border-top-left-radius: 12px !important;
        border-bottom-left-radius: 12px !important;
        font-weight: 700;
        padding-left: 1rem;
        padding-right: 1rem;
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
        <div class="d-flex align-items-center mb-4">
            <div class="icon-header-box text-white me-3 shadow-sm">
                <i class="fas fa-plus"></i>
            </div>
            <div>
                <h3 class="fw-extrabold text-dark mb-0">Tambah Paket Travel</h3>
                <p class="mb-0 text-muted small">Tambahkan paket perjalanan Haji atau Umroh baru ke katalog</p>
            </div>
        </div>

        <!-- Form Card Container -->
        <div class="card form-card border-0 shadow-sm bg-white">
            <div class="card-body p-4">
                <div class="d-flex align-items-center fw-bold mb-3" style="color: var(--primary-emerald); font-size: 1.05rem;">
                    <i class="fas fa-kaaba me-2"></i> Informasi Paket Travel
                </div>
                <hr class="mt-0 mb-4" style="border-color: #e2e8f0;">

                <form action="proses_tambah_paket.php" method="POST">
                    <div class="row g-3">
                        <!-- Nama Paket -->
                        <div class="col-md-8 mb-2">
                            <label for="nama_paket" class="form-label fw-semibold">Nama Paket <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_paket" name="nama_paket"
                                placeholder="Contoh: Paket Umroh VIP Ramadhan" required>
                        </div>

                        <!-- Jenis Paket -->
                        <div class="col-md-4 mb-2">
                            <label for="jenis_paket" class="form-label fw-semibold">Jenis Paket <span class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_paket" name="jenis_paket" required>
                                <option value="" selected disabled>-- Pilih Jenis --</option>
                                <option value="Haji">Haji</option>
                                <option value="Umroh">Umroh</option>
                            </select>
                        </div>

                        <!-- Harga Paket -->
                        <div class="col-md-6 mb-2">
                            <label for="harga_display" class="form-label fw-semibold">Harga Paket (Per PAX) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-emerald">Rp</span>

                                <!-- Input Tampilan (Rupiah Formatted) -->
                                <input type="text" class="form-control" id="harga_display" inputmode="numeric"
                                    placeholder="Contoh: 35.000.000" required>

                                <!-- Input Hidden (Nilai Asli) -->
                                <input type="hidden" id="harga" name="harga">
                            </div>
                        </div>

                        <!-- Durasi Paket -->
                        <div class="col-md-3 mb-2">
                            <label for="durasi" class="form-label fw-semibold">Durasi Perjalanan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="durasi" name="durasi" min="1"
                                    placeholder="Contoh: 9" required>
                                <span class="input-group-text bg-light text-muted fw-semibold" style="border-top-right-radius: 12px; border-bottom-right-radius: 12px;">Hari</span>
                            </div>
                        </div>

                        <!-- Kuota Jamaah -->
                        <div class="col-md-3 mb-2">
                            <label for="kuota" class="form-label fw-semibold">Kuota Jamaah <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="kuota" name="kuota" min="1"
                                    oninput="if(this.value < 1) this.value = 1;" placeholder="Contoh: 45" required>
                                <span class="input-group-text bg-light text-muted fw-semibold" style="border-top-right-radius: 12px; border-bottom-right-radius: 12px;">Jamaah</span>
                            </div>
                        </div>

                        <!-- Deskripsi / Fasilitas -->
                        <div class="col-12 mb-3">
                            <label for="deskripsi" class="form-label fw-semibold">Deskripsi & Fasilitas Paket</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"
                                placeholder="Jelaskan fasilitas tercover, seperti hotel, pesawat, bus, dan konsumsi..."></textarea>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="tabel_paket.php" class="btn btn-cancel px-4 py-2 d-flex align-items-center gap-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-gold px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                            <i class="fas fa-save"></i> Simpan Paket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hargaDisplay = document.getElementById('harga_display');
        const hargaReal = document.getElementById('harga');

        if (hargaDisplay && hargaReal) {
            hargaDisplay.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                hargaReal.value = value;
                this.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
            });
        }
    });
</script>
?>