<!-- form_tambah_pendaftaran.php / form_tambah_jadwal.php -->
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

// Tanggal hari ini (YYYY-MM-DD)
$today = date('Y-m-d');
// Minimal kepulangan awal (H+9 dari hari ini)
$minPulangAwal = date('Y-m-d', strtotime($today . ' +9 days'));

// Ambil data paket travel untuk pilihan dropdown
$paketList = [];
try {
    $queryPaket = "SELECT id, nama_paket FROM paket ORDER BY nama_paket ASC";
    $stmtPaket = $db->prepare($queryPaket);
    $stmtPaket->execute();
    $paketList = $stmtPaket->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Abaikan jika tabel belum siap
}

$pendaftaran_id = $_GET['id_pendaftaran'] ?? null;

$dataPendaftaran = ['id' => '', 'paket_id' => '', 'nama_paket' => ''];
if ($pendaftaran_id) {
    $query = "SELECT pendaftaran.id, paket.id as paket_id, paket.nama_paket 
              FROM pendaftaran 
              JOIN paket ON pendaftaran.paket_id = paket.id 
              WHERE pendaftaran.id = :id";
    $stmtPendaftaran = $db->prepare($query);
    $stmtPendaftaran->execute([":id" => $pendaftaran_id]);
    $dataPendaftaran = $stmtPendaftaran->fetch(PDO::FETCH_ASSOC) ?: $dataPendaftaran;
}

include "components/header.php";
include "components/sidebar.php";
?>

<!-- Import Google Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-emerald: #1a4d36;
        --secondary-emerald: #236346;
        --accent-orange: #c86d18;
        --accent-gold: #d4a359;
        --bg-warm: #f9f6f0;
        --text-dark: #2c2825;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: var(--bg-warm);
    }

    .icon-header-box {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background-color: var(--primary-emerald);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .form-card {
        border-radius: 20px;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .form-label {
        color: var(--text-dark);
        font-size: 0.85rem;
    }

    .form-control,
    .form-select {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 0.65rem 0.9rem;
        font-size: 0.925rem;
        transition: all 0.2s ease;
        background-color: #ffffff;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-emerald);
        box-shadow: 0 0 0 4px rgba(26, 77, 54, 0.1);
    }

    .btn-submit-theme {
        background-color: var(--accent-orange);
        color: #ffffff !important;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-submit-theme:hover {
        opacity: 0.92;
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(200, 109, 24, 0.25);
    }

    .btn-cancel {
        background-color: #ffffff;
        color: #64748b;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-cancel:hover {
        background-color: #f1f5f9;
        color: #1e293b;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Judul -->
        <div class="d-flex align-items-center mb-4">
            <div class="icon-header-box text-white me-3 shadow-sm">
                <i class="fas fa-plane-departure"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: var(--text-dark);">Tambah Jadwal Keberangkatan</h3>
                <p class="mb-0 text-muted small">Tambahkan tanggal penerbangan, maskapai, dan kuota jamaah baru</p>
            </div>
        </div>

        <!-- Form Card Container -->
        <div class="card form-card border-0 shadow-sm bg-white">
            <div class="card-body p-4">
                <div class="d-flex align-items-center fw-bold mb-3" style="color: var(--primary-emerald); font-size: 1.05rem;">
                    <i class="fas fa-calendar-alt me-2"></i> Detail Jadwal Penerbangan
                </div>
                <hr class="mt-0 mb-4" style="border-color: #f1f5f9;">

                <form action="proses_tambah_jadwal.php" method="POST" id="formTambahJadwal">
                    <div class="row g-3">

                        <input type="hidden" name="pendaftaran_id" value="<?= htmlspecialchars($dataPendaftaran['id']); ?>">

                        <!-- Pilih Paket Travel -->
                        <div class="col-md-12 mb-2">
                            <label for="paket_id" class="form-label fw-semibold">Paket Travel <span class="text-danger">*</span></label>
                            <input type="hidden" name="paket_id" value="<?= htmlspecialchars($dataPendaftaran['paket_id']); ?>">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($dataPendaftaran['nama_paket']); ?>" disabled>
                        </div>

                        <!-- Tanggal Keberangkatan (Minimal Hari Ini) -->
                        <div class="col-md-6 mb-2">
                            <label for="tanggal_berangkat" class="form-label fw-semibold">Tanggal Keberangkatan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_berangkat" name="tanggal_berangkat"
                                min="<?= $today; ?>" onkeydown="return false;" required>
                        </div>

                        <!-- Tanggal Kepulangan (Minimal H+9) -->
                        <div class="col-md-6 mb-2">
                            <label for="tgl_kepulangan" class="form-label fw-semibold">Tanggal Kepulangan (Estimasi Min. H+9)</label>
                            <input type="date" class="form-control" id="tgl_kepulangan" name="tgl_kepulangan"
                                min="<?= $minPulangAwal; ?>" onkeydown="return false;">
                        </div>

                        <!-- Nama Maskapai (Dropdown) -->
                        <div class="col-md-6 mb-2">
                            <label for="maskapai" class="form-label fw-semibold">Nama Maskapai <span class="text-danger">*</span></label>
                            <select class="form-select" id="maskapai" name="maskapai" required>
                                <option value="" selected disabled>-- Pilih Maskapai --</option>
                                <option value="Saudia Airlines">Saudia Airlines</option>
                                <option value="Garuda Indonesia">Garuda Indonesia</option>
                                <option value="Qatar Airways">Qatar Airways</option>
                                <option value="Emirates">Emirates</option>
                                <option value="Etihad Airways">Etihad Airways</option>
                                <option value="Lion Air">Lion Air</option>
                                <option value="Batik Air">Batik Air</option>
                                <option value="Oman Air">Oman Air</option>
                            </select>
                        </div>

                        <!-- Embarkasi / Bandara (Dropdown) -->
                        <div class="col-md-3 mb-2">
                            <label for="embarkasi" class="form-label fw-semibold">Embarkasi / Bandara <span class="text-danger">*</span></label>
                            <select class="form-select" id="embarkasi" name="embarkasi" required>
                                <option value="" selected disabled>-- Pilih Embarkasi --</option>
                                <option value="Jakarta (CGK)">Jakarta (CGK)</option>
                                <option value="Surabaya (SUB)">Surabaya (SUB)</option>
                                <option value="Medan (KNO)">Medan (KNO)</option>
                                <option value="Makassar (UPG)">Makassar (UPG)</option>
                                <option value="Solo (SOC)">Solo (SOC)</option>
                                <option value="Kertajati (KJT)">Kertajati (KJT)</option>
                                <option value="Padang (PDG)">Padang (PDG)</option>
                                <option value="Palembang (PLM)">Palembang (PLM)</option>
                            </select>
                        </div>

                        <!-- Kuota Penerbangan (Dropdown) -->
                        <div class="col-md-3 mb-2">
                            <label for="kuota" class="form-label fw-semibold">Kuota Penerbangan <span class="text-danger">*</span></label>
                            <select class="form-select" id="kuota" name="kuota" required>
                                <option value="" selected disabled>-- Pilih Kuota --</option>
                                <option value="20">20 Pax</option>
                                <option value="30">30 Pax</option>
                                <option value="40">40 Pax</option>
                                <option value="45">45 Pax</option>
                                <option value="50">50 Pax</option>
                                <option value="60">60 Pax</option>
                                <option value="90">90 Pax</option>
                            </select>
                        </div>

                        <!-- Catatan / Keterangan -->
                        <div class="col-12 mb-3">
                            <label for="keterangan" class="form-label fw-semibold">Catatan / Keterangan Tambahan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                placeholder="Tambahkan instruksi berkumpul di bandara, jam check-in, atau info penting lainnya..."></textarea>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top" style="border-color: #f1f5f9;">
                        <a href="tabel_keberangkatan.php" class="btn btn-cancel px-4 py-2 d-flex align-items-center gap-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-submit-theme px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                            <i class="fas fa-save"></i> Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("formTambahJadwal");
        const inputBerangkat = document.getElementById("tanggal_berangkat");
        const inputKepulangan = document.getElementById("tgl_kepulangan");

        // Fungsi menambah jumlah hari aman dari timezone offset
        function addDays(dateStr, days) {
            if (!dateStr) return "";
            const parts = dateStr.split("-");
            const date = new Date(parts[0], parts[1] - 1, parts[2]);
            date.setDate(date.getDate() + days);

            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        // Ketika user memilih tanggal keberangkatan
        inputBerangkat.addEventListener("change", function() {
            const valBerangkat = this.value;
            if (valBerangkat) {
                // Hitung tanggal minimal kepulangan (H+9 hari)
                const minPulangVal = addDays(valBerangkat, 9);
                inputKepulangan.min = minPulangVal;

                // Jika tanggal kepulangan belum diisi ATAU kurang dari H+9, set otomatis ke H+9
                if (!inputKepulangan.value || inputKepulangan.value < minPulangVal) {
                    inputKepulangan.value = minPulangVal;
                }
            }
        });

        // Validasi saat Form Disubmit
        form.addEventListener("submit", function(e) {
            const valBerangkat = inputBerangkat.value;
            const valPulang = inputKepulangan.value;
            const todayStr = "<?= $today; ?>";

            if (valBerangkat < todayStr) {
                alert("Tanggal keberangkatan tidak boleh sebelum hari ini!");
                e.preventDefault();
                return false;
            }

            if (valPulang) {
                const minPulangHarus = addDays(valBerangkat, 9);
                if (valPulang < minPulangHarus) {
                    alert("Tanggal kepulangan minimal harus berjarak 9 hari dari tanggal keberangkatan!");
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>

<?php
include "components/footer.php";
include "components/bottom.php";
?>