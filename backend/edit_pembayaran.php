<!-- edit_pembayaran.php -->
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

// Izinkan admin, petugas, dan jamaah untuk mengakses halaman ini
$auth->checkRole(['admin', 'petugas']);

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: tabel_pembayaran.php");
    exit;
}

// Query mengambil data pendaftaran & pembayaran yang sesuai
$query = "SELECT
            p.id AS pendaftaran_id,
            p.status AS status_pendaftaran,
            p.jamaah_id,
            j.nama_lengkap,
            pk.nama_paket,
            COALESCE(pk.harga, 0) AS harga_paket,
            pm.id AS pembayaran_id,
            pm.sisa_pembayaran,
            pm.nominal,
            pm.tanggal_bayar,
            pm.status AS status_pembayaran
          FROM pembayaran pm
          LEFT JOIN pendaftaran p ON pm.pendaftaran_id = p.id
          LEFT JOIN jamaah j ON pm.jamaah_id = j.id
          LEFT JOIN paket pk ON p.paket_id = pk.id
          WHERE pm.id = :id
          LIMIT 1";

$stmt = $db->prepare($query);
$stmt->execute([':id' => $id]);

$pembayaran = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pembayaran) {
    header("Location: tabel_pembayaran.php");
    exit;
}

// Perhitungan Sisa Pembayaran Default
$hargaPaket = (float)($pembayaran['harga_paket'] ?? 0);
$nominalDibayar = (float)($pembayaran['nominal'] ?? 0);
$sisaPembayaranDefault = isset($pembayaran['sisa_pembayaran']) && $pembayaran['sisa_pembayaran'] !== null
    ? $pembayaran['sisa_pembayaran']
    : max(0, $hargaPaket - $nominalDibayar);

include "components/header.php";
include "components/sidebar.php";
?>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <div class="d-flex align-items-center mb-4">
            <div class="p-3 rounded-3 d-flex align-items-center justify-content-center me-3" style="background-color: #1a4d36; color: #ffffff; width: 48px; height: 48px;">
                <i class="fas fa-edit fa-lg"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: #2c2825;">Update Sisa & Status Pembayaran</h3>
                <p class="mb-0 small" style="color: #78716c;">Perbarui nilai sisa pembayaran dan kelola status transaksi jamaah.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center fw-bold mb-3" style="color: #1a4d36;">
                    <i class="fas fa-receipt me-2"></i> Detail Transaksi Pembayaran
                </div>
                <hr class="mt-0 mb-4" style="border-color: #e7e5e4;">

                <form action="proses_update_pembayaran.php" method="POST">
                    <input type="hidden" name="pembayaran_id" value="<?= htmlspecialchars($pembayaran['pembayaran_id'] ?? ''); ?>">
                    <input type="hidden" name="pendaftaran_id" value="<?= htmlspecialchars($pembayaran['pendaftaran_id'] ?? ''); ?>">

                    <!-- Nama Jamaah (Read-Only) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Jamaah</label>
                        <input type="text" class="form-control form-control-lg fs-6 bg-light text-muted" value="<?= htmlspecialchars($pembayaran['nama_lengkap'] ?? '-'); ?>" readonly disabled>
                    </div>

                    <!-- Program / Paket (Read-Only) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Program & Layanan</label>
                        <input type="text" class="form-control form-control-lg fs-6 bg-light text-muted" value="<?= htmlspecialchars($pembayaran['nama_paket'] ?? 'Paket Haji/Umroh'); ?>" readonly disabled>
                    </div>

                    <!-- Total Biaya Paket (Read-Only) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Total Biaya Paket</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-secondary text-white border-0 fw-bold fs-6">Rp</span>
                            <input type="text" class="form-control fs-6 bg-light text-muted" value="<?= number_format($hargaPaket, 0, ',', '.'); ?>" readonly disabled>
                        </div>
                    </div>

                    <!-- Status Pembayaran -->
                    <div class="mb-3">
                        <label for="status" class="form-label small fw-bold" style="color: #1a4d36;">Status Pembayaran</label>
                        <select name="status" id="status" class="form-select form-select-lg fs-6" required>
                            <option value="Pending" <?= in_array($pembayaran['status_pembayaran'] ?? '', ['Pending', '']) ? 'selected' : ''; ?>>Pending / Menunggu Verifikasi</option>
                            <option value="Lunas" <?= ($pembayaran['status_pembayaran'] ?? '' === 'Valid' && (float)($pembayaran['sisa_pembayaran'] ?? 0) == 0) || $pembayaran['status_pembayaran'] ?? '' === 'Lunas' ? 'selected' : ''; ?>>Lunas / Full</option>
                        </select>
                    </div>

                    <!-- SISA PEMBAYARAN -->
                    <div class="mb-4">
                        <label for="sisa_pembayaran_display" class="form-label small fw-bold" style="color: #1a4d36;">Sisa Pembayaran (Rp)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text fw-bold fs-6" style="background-color: #1a4d36; color: #ffffff; border: none;">Rp</span>
                            <input
                                type="text"
                                class="form-control fs-6 fw-bold text-danger"
                                id="sisa_pembayaran_display"
                                inputmode="numeric"
                                placeholder="Contoh: 5.000.000"
                                value="<?= number_format($sisaPembayaranDefault, 0, ',', '.'); ?>"
                                required>
                            <input
                                type="hidden"
                                id="sisa_pembayaran"
                                name="sisa_pembayaran"
                                value="<?= htmlspecialchars($sisaPembayaranDefault); ?>">
                        </div>
                        <small class="text-muted fs-7 mt-1 d-block">*Apabila status diubah ke Lunas, sisa pembayaran disarankan menjadi 0.</small>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="tabel_pembayaran.php" class="btn border btn-md px-4 fw-medium" style="background-color: #f5f5f4; color: #57534e;">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-md px-4 fw-bold shadow-sm" style="background-color: #d4a359; color: #ffffff; border: none;">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sisaDisplay = document.getElementById('sisa_pembayaran_display');
        const sisaReal = document.getElementById('sisa_pembayaran');
        const statusSelect = document.getElementById('status');

        if (sisaDisplay && sisaReal) {
            sisaDisplay.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                sisaReal.value = value ? value : '0';
                this.value = value ? new Intl.NumberFormat('id-ID').format(value) : '0';
            });
        }

        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                if (this.value === 'Lunas') {
                    sisaReal.value = '0';
                    sisaDisplay.value = '0';
                }
            });
        }
    });
</script>
?>