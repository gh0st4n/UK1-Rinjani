<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin']);

include "components/header.php";
include "components/sidebar.php";
?>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body">
        <div class="d-flex align-items-center mb-4">
            <div class="p-3 rounded-3 d-flex align-items-center justify-content-center me-3" style="background-color: #1a4d36; color: #ffffff; width: 48px; height: 48px;">
                <i class="fas fa-user-plus fa-lg"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: #2c2825;">Tambah User / Admin</h3>
                <p class="mb-0 small" style="color: #78716c;">Buat akun petugas atau admin baru untuk akses sistem</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center fw-bold mb-3" style="color: #1a4d36;">
                    <i class="fas fa-user-shield me-2"></i> Form Pengguna Baru
                </div>
                <hr class="mt-0 mb-4" style="border-color: #e7e5e4;">

                <form action="proses_tambah_user.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label small fw-semibold" style="color: #44403c;">Username</label>
                        <input type="text" class="form-control form-control-lg fs-6" id="username" name="username" placeholder="Masukkan username" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold" style="color: #44403c;">Password</label>
                        <input type="password" class="form-control form-control-lg fs-6" id="password" name="password" placeholder="Masukkan password" required>
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label small fw-semibold" style="color: #44403c;">Role Hak Akses</label>
                        <input type="text" class="form-control form-control-lg fs-6 bg-light" value="Petugas" readonly>
                        <input type="hidden" name="role" value="petugas">
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="tabel_user.php" class="btn border btn-md px-4 fw-medium" style="background-color: #f5f5f4; color: #57534e;">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-md px-4 fw-bold shadow-sm" style="background-color: #d4a359; color: #ffffff; border: none;">
                            <i class="fas fa-save me-1"></i> Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include "components/footer.php";
include "components/bottom.php";

?>