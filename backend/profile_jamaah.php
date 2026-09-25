
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

$auth->checkRole(['jamaah']);

$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

$successMessage = "";
$errorMessage = "";

// 1. Ambil Data Jamaah
$jamaahData = [];
try {
    $stmt = $db->prepare("SELECT j.*, u.username 
                          FROM jamaah j 
                          JOIN user u ON j.user_id = u.id 
                          WHERE j.user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $jamaahData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$jamaahData) {
        $jamaahData = [
            'id'            => '',
            'nama_lengkap'  => $_SESSION['username'] ?? '',
            'no_hp'         => '',
            'nik'           => '',
            'jenis_kelamin' => 'L',
            'alamat'        => '',
            'username'      => $_SESSION['username'] ?? 'jamaah'
        ];
    }
} catch (PDOException $e) {
    $errorMessage = "Gagal mengambil data profil: " . $e->getMessage();
}

// 2. Proses Edit Profil (No HP, Alamat, Jenis Kelamin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $noHp         = htmlspecialchars(trim($_POST['no_hp'] ?? ''));
    $alamat       = htmlspecialchars(trim($_POST['alamat'] ?? ''));
    $jenisKelamin = htmlspecialchars(trim($_POST['jenis_kelamin'] ?? 'L'));

    try {
        if (!empty($jamaahData['id'])) {
            $updateSql = "UPDATE jamaah SET no_hp = :hp, alamat = :alamat, jenis_kelamin = :jk WHERE id = :id";
            $stmtUpdate = $db->prepare($updateSql);
            $stmtUpdate->execute([
                ':hp'     => $noHp,
                ':alamat' => $alamat,
                ':jk'     => $jenisKelamin,
                ':id'     => $jamaahData['id']
            ]);
            
            $successMessage = "Profil berhasil diperbarui!";
            $jamaahData['no_hp']         = $noHp;
            $jamaahData['alamat']        = $alamat;
            $jamaahData['jenis_kelamin'] = $jenisKelamin;
        }
    } catch (PDOException $e) {
        $errorMessage = "Gagal memperbarui profil: " . $e->getMessage();
    }
}

// 3. Proses Ganti Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $passLama = htmlspecialchars(trim($_POST['password_lama'] ?? ''));
    $passBaru = htmlspecialchars(trim($_POST['password_baru'] ?? ''));
    $konfirm  = htmlspecialchars(trim($_POST['konfirmasi_password'] ?? ''));

    if (empty($passLama) || empty($passBaru) || empty($konfirm)) {
        $errorMessage = "Semua kolom password wajib diisi!";
    } elseif ($passBaru !== $konfirm) {
        $errorMessage = "Konfirmasi password baru tidak cocok!";
    } else {
        try {
            $stmtUser = $db->prepare("SELECT password FROM user WHERE id = :id");
            $stmtUser->execute([':id' => $userId]);
            $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($userRow && password_verify($passLama, $userRow['password'])) {
                $newHash = password_hash($passBaru, PASSWORD_BCRYPT);
                $stmtPass = $db->prepare("UPDATE user SET password = :pass WHERE id = :id");
                $stmtPass->execute([':pass' => $newHash, ':id' => $userId]);

                $successMessage = "Password berhasil diperbarui!";
            } else {
                $errorMessage = "Password lama Anda salah!";
            }
        } catch (PDOException $e) {
            $errorMessage = "Gagal mengubah password: " . $e->getMessage();
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
        --bg-modern: #f8fafc;
    }
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: var(--bg-modern);
    }
    .icon-header-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .profile-card {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
    }
    .avatar-box {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
        color: #ffffff;
        font-size: 2.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px auto;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        padding: 12px 20px;
        border-bottom: 2px solid transparent;
    }
    .nav-tabs .nav-link.active {
        color: #047857;
        background-color: transparent;
        border-bottom: 2px solid #047857;
    }
    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%);
        color: #ffffff;
        font-weight: 700;
        border: none;
    }
    .btn-gold:hover {
        background: linear-gradient(135deg, #c29d26 0%, #996515 100%);
        color: #ffffff;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <div class="d-flex align-items-center mb-4">
            <div class="icon-header-box text-white me-3 shadow-sm">
                <i class="fas fa-user-circle"></i>
            </div>
            <div>
                <h4 class="fw-bold text-dark mb-0">Profil Saya</h4>
                <p class="mb-0 text-muted small">Kelola data diri dan keamanan akun Anda</p>
            </div>
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

        <div class="row g-4">
            <!-- Kartu Ringkasan Kiri -->
            <div class="col-lg-4">
                <div class="card profile-card border-0 shadow-sm bg-white p-4 text-center">
                    <div class="avatar-box shadow-sm">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($jamaahData['nama_lengkap'] ?: ($jamaahData['username'] ?? 'Jamaah')); ?></h5>
                    <p class="text-muted small mb-3">@<?= htmlspecialchars($jamaahData['username'] ?? 'jamaah'); ?></p>
                    
                    <span class="badge rounded-pill px-3 py-2 align-self-center mb-3" style="background-color: #d1fae5; color: #047857; font-size: 11px;">
                        <i class="fas fa-shield-alt me-1"></i> Akun Jamaah Terverifikasi
                    </span>

                    <hr class="my-3">

                    <div class="text-start small">
                        <div class="mb-2">
                            <span class="text-muted d-block">Nomor HP / WA:</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($jamaahData['no_hp'] ?: '-'); ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted d-block">NIK / No. KTP:</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($jamaahData['nik'] ?: '-'); ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted d-block">Jenis Kelamin:</span>
                            <span class="fw-semibold text-dark"><?= (strtoupper($jamaahData['jenis_kelamin'] ?? 'L') === 'P') ? 'Perempuan' : 'Laki-Laki'; ?></span>
                        </div>
                        <div class="mb-0">
                            <span class="text-muted d-block">Alamat:</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($jamaahData['alamat'] ?: '-'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Tab Form Kanan -->
            <div class="col-lg-8">
                <div class="card profile-card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white border-bottom px-4 pt-3 pb-0">
                        <ul class="nav nav-tabs card-header-tabs" id="profileTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit-panel" type="button" role="tab">
                                    <i class="fas fa-user-edit me-2"></i>Edit Data Diri
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-panel" type="button" role="tab">
                                    <i class="fas fa-key me-2"></i>Ganti Password
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4 p-md-4">
                        <div class="tab-content" id="profileTabContent">
                            
                            <!-- TAB 1: EDIT PROFIL -->
                            <div class="tab-pane fade show active" id="edit-panel" role="tabpanel">
                                <form action="" method="POST">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-secondary small">Nama Lengkap</label>
                                            <input type="text" class="form-control py-2 rounded-3 bg-light" value="<?= htmlspecialchars($jamaahData['nama_lengkap'] ?? ''); ?>" readonly>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-secondary small">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                                            <input type="text" name="no_hp" class="form-control py-2 rounded-3" value="<?= htmlspecialchars($jamaahData['no_hp'] ?? ''); ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-secondary small">NIK / Nomor KTP</label>
                                            <input type="text" class="form-control py-2 rounded-3 bg-light" value="<?= htmlspecialchars($jamaahData['nik'] ?? ''); ?>" readonly>
                                        </div>

                                      <div class="col-md-6">
    <label class="form-label fw-semibold text-secondary small">Jenis Kelamin</label>
    
    <!-- Input Tampilan (Readonly) -->
    <?php 
        $jkVal = strtoupper($jamaahData['jenis_kelamin'] ?? 'L');
        $jkTeks = ($jkVal === 'P') ? 'Perempuan' : 'Laki-Laki';
    ?>
    <input type="text" class="form-control py-2 rounded-3 bg-light" value="<?= $jkTeks; ?>" readonly disabled>
    
    <!-- Hidden Input agar nilainya tetap terkirim saat form submit -->
    <input type="hidden" name="jenis_kelamin" value="<?= $jkVal; ?>">
</div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-secondary small">Alamat Lengkap <span class="text-danger">*</span></label>
                                            <textarea name="alamat" class="form-control rounded-3" rows="3" required><?= htmlspecialchars($jamaahData['alamat'] ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="submit" name="update_profile" class="btn btn-gold px-4 py-2 rounded-3 shadow-sm">
                                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- TAB 2: GANTI PASSWORD -->
                            <div class="tab-pane fade" id="password-panel" role="tabpanel">
                                <form action="" method="POST">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-secondary small">Password Lama <span class="text-danger">*</span></label>
                                            <input type="password" name="password_lama" class="form-control py-2 rounded-3" placeholder="Masukkan password saat ini" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-secondary small">Password Baru <span class="text-danger">*</span></label>
                                            <input type="password" name="password_baru" class="form-control py-2 rounded-3" placeholder="Masukkan password baru" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-secondary small">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                            <input type="password" name="konfirmasi_password" class="form-control py-2 rounded-3" placeholder="Ulangi password baru" required>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="submit" name="update_password" class="btn btn-dark px-4 py-2 rounded-3 shadow-sm">
                                            <i class="fas fa-lock me-1"></i> Perbarui Password
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
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