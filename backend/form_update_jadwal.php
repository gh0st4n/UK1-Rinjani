<!-- form_update_pendaftaran.php -->
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: tabel_keberangkatan.php");
    exit();
}

// Fetch data keberangkatan berdasarkan ID
$stmt = $db->prepare("SELECT * FROM keberangkatan WHERE id = :id LIMIT 1");
$stmt->bindParam(':id', $id);
$stmt->execute();
$jadwal = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika tidak ditemukan di tabel keberangkatan, coba tabel jadwal_keberangkatan
if (!$jadwal) {
    $stmt = $db->prepare("SELECT * FROM jadwal_keberangkatan WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $jadwal = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$jadwal) {
    echo "<script>alert('Data jadwal keberangkatan tidak ditemukan!'); window.location.href='tabel_keberangkatan.php';</script>";
    exit();
}

// Fetch daftar paket travel untuk dropdown
$stmtPaket = $db->prepare("SELECT id, nama_paket FROM paket ORDER BY nama_paket ASC");
$stmtPaket->execute();
$paketList = $stmtPaket->fetchAll(PDO::FETCH_ASSOC);

// Tanggal hari ini (YYYY-MM-DD)
$today = date('Y-m-d');

// Mengambil nilai tanggal keberangkatan & kepulangan dari DB
$tglBerangkat = $jadwal['tanggal_berangkat'] ?? $jadwal['tgl_keberangkatan'] ?? '';
$tglPulang    = $jadwal['tgl_kepulangan'] ?? $jadwal['tanggal_kepulangan'] ?? '';

// Menentukan batas minimum tanggal keberangkatan
// Jika tanggal lama di DB sudah lewat, izinkan minimal sesuai tanggal DB agar tidak merusak data lama
$minBerangkat = (!empty($tglBerangkat) && $tglBerangkat < $today) ? $tglBerangkat : $today;

// Menghitung tanggal minimum kepulangan (minimal H+9 dari tanggal keberangkatan saat ini)
$refBerangkat = !empty($tglBerangkat) ? $tglBerangkat : $today;
$minPulang    = date('Y-m-d', strtotime($refBerangkat . ' +9 days'));

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
    }

    .form-control,
    .form-select {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 0.65rem 0.9rem;
        font-size: 0.925rem;
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
    }

    .btn-cancel {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-weight: 600;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman -->
        <div class="d-flex align-items-center mb-4">
            <div class="icon-header-box text-white me-3 shadow-sm">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <h3 class="fw-extrabold text-dark mb-0">Edit Jadwal Keberangkatan</h3>
                <p class="mb-0 text-muted small">Perbarui informasi tanggal penerbangan, maskapai, dan kuota jamaah</p>
            </div>
        </div>

        <!-- Form Card Container -->
        <div class="card form-card border-0 shadow-sm bg-white">
            <div class="card-body p-4">
                <form action="proses_update_jadwal.php" method="POST" id="formJadwal">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($jadwal['id']); ?>">

                    <div class="row g-3">
                        <!-- Pilih Paket Travel -->
                        <div class="col-12 mb-2">
                            <label for="paket_id" class="form-label fw-semibold">Pilih Paket Travel <span class="text-danger">*</span></label>
                            <select class="form-select" id="paket_id" name="paket_id" required>
                                <option value="" disabled>-- Pilih Paket Haji / Umroh --</option>
                                <?php foreach ($paketList as $p): ?>
                                    <option value="<?= $p['id']; ?>" <?= ($p['id'] == $jadwal['paket_id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($p['nama_paket']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tanggal Keberangkatan -->
                        <div class="col-md-6 mb-2">
                            <label for="tanggal_berangkat" class="form-label fw-semibold">Tanggal Keberangkatan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_berangkat" name="tanggal_berangkat"
                                min="<?= $minBerangkat; ?>"
                                value="<?= htmlspecialchars($tglBerangkat); ?>"
                                onkeydown="return false;" required>
                        </div>

                        <!-- Tanggal Kepulangan -->
                        <div class="col-md-6 mb-2">
                            <label for="tgl_kepulangan" class="form-label fw-semibold">Tanggal Kepulangan (Estimasi Min. H+9)</label>
                            <input type="date" class="form-control" id="tgl_kepulangan" name="tgl_kepulangan"
                                min="<?= $minPulang; ?>"
                                value="<?= htmlspecialchars($tglPulang); ?>"
                                onkeydown="return false;">
                        </div>

                        <!-- Nama Maskapai (Dropdown agar sama dengan Form Tambah) -->
                        <div class="col-md-4 mb-2">
                            <label for="maskapai" class="form-label fw-semibold">Nama Maskapai <span class="text-danger">*</span></label>
                            <?php $currMaskapai = $jadwal['maskapai'] ?? ''; ?>
                            <select class="form-select" id="maskapai" name="maskapai" required>
                                <option value="" disabled>-- Pilih Maskapai --</option>
                                <?php
                                $maskapaiArr = ['Saudia Airlines', 'Garuda Indonesia', 'Qatar Airways', 'Emirates', 'Etihad Airways', 'Lion Air', 'Batik Air', 'Oman Air'];
                                foreach ($maskapaiArr as $m):
                                ?>
                                    <option value="<?= $m; ?>" <?= ($currMaskapai == $m) ? 'selected' : ''; ?>><?= $m; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Embarkasi / Bandara (Dropdown) -->
                        <div class="col-md-4 mb-2">
                            <label for="embarkasi" class="form-label fw-semibold">Embarkasi / Bandara <span class="text-danger">*</span></label>
                            <?php $currEmbarkasi = $jadwal['embarkasi'] ?? ''; ?>
                            <select class="form-select" id="embarkasi" name="embarkasi" required>
                                <option value="" disabled>-- Pilih Embarkasi --</option>
                                <?php
                                $embarkasiArr = ['Jakarta (CGK)', 'Surabaya (SUB)', 'Medan (KNO)', 'Makassar (UPG)', 'Solo (SOC)', 'Kertajati (KJT)', 'Padang (PDG)', 'Palembang (PLM)'];
                                foreach ($embarkasiArr as $emb):
                                ?>
                                    <option value="<?= $emb; ?>" <?= ($currEmbarkasi == $emb) ? 'selected' : ''; ?>><?= $emb; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Kuota Penerbangan (Dropdown) -->
                        <div class="col-md-4 mb-2">
                            <label for="kuota" class="form-label fw-semibold">Kuota Penerbangan <span class="text-danger">*</span></label>
                            <?php $currKuota = $jadwal['kuota_penerbangan'] ?? $jadwal['kuota'] ?? ''; ?>
                            <select class="form-select" id="kuota" name="kuota" required>
                                <option value="" disabled>-- Pilih Kuota --</option>
                                <?php
                                $kuotaArr = ['20', '30', '40', '45', '50', '60', '90'];
                                foreach ($kuotaArr as $k):
                                ?>
                                    <option value="<?= $k; ?>" <?= ($currKuota == $k) ? 'selected' : ''; ?>><?= $k; ?> Pax</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Catatan / Keterangan Tambahan -->
                        <div class="col-12 mb-3">
                            <label for="keterangan" class="form-label fw-semibold">Catatan / Keterangan Tambahan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                placeholder="Tambahkan instruksi berkumpul di bandara, jam check-in, atau info penting lainnya..."><?= htmlspecialchars($jadwal['keterangan'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="tabel_keberangkatan.php" class="btn btn-cancel px-4 py-2 d-flex align-items-center gap-2">
                            <i class="fas fa-arrow-left"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-gold px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("formJadwal");
        const inputBerangkat = document.getElementById("tanggal_berangkat");
        const inputKepulangan = document.getElementById("tgl_kepulangan");

        // Tanggal asal dari database sebelum diedit
        const originalBerangkatVal = "<?= $tglBerangkat; ?>";

        // Fungsi menambah hari berdasarkan format YYYY-MM-DD
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

        // Ketika tanggal keberangkatan diubah oleh pengguna
        inputBerangkat.addEventListener("change", function() {
            const valBerangkat = this.value;
            if (valBerangkat) {
                // Hitung tanggal minimal kepulangan (H+9 hari)
                const minPulangVal = addDays(valBerangkat, 9);
                inputKepulangan.min = minPulangVal;

                // Jika tanggal kepulangan terisi namun kurang dari H+9, sesuaikan otomatis ke H+9
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

            // Hanya cegah jika user sengaja MENGUBAH tanggal ke tanggal sebelum hari ini,
            // namun izinkan jika tanggal tersebut memang data awal/lama yang dari DB.
            if (valBerangkat < todayStr && valBerangkat !== originalBerangkatVal) {
                alert("Tanggal keberangkatan baru tidak boleh sebelum hari ini!");
                e.preventDefault();
                return false;
            }

            // Validasi tanggal kepulangan minimal berjarak 9 hari dari keberangkatan
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