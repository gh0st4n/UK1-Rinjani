<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';

// Helper function untuk menentukan class active
function isActive($pages, $current_page)
{
    if (is_array($pages)) {
        return in_array($current_page, $pages) ? 'active' : '';
    }
    return $current_page === $pages ? 'active' : '';
}
?>

<style>
    .sidebar {
        width: 260px;
        background-color: #f4efe6 !important;
        color: #2c2825;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-self: stretch;
        z-index: 1050;
    }

    .sidebar-brand {
        padding: 25px 20px 20px;
        text-align: center;
        border-bottom: 1px solid #e0d6c5;
    }

    .sidebar-brand-icon {
        width: 52px;
        height: 52px;
        background: #1a1a1a;
        border: 2px solid #c5a059;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        color: #c5a059;
        font-size: 22px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .sidebar-brand h4 {
        font-size: 15px;
        font-weight: 800;
        letter-spacing: 0.5px;
        margin: 0;
        color: #1a1a1a;
    }

    .sidebar-brand p {
        font-size: 11px;
        color: #6c635b;
        margin: 0;
    }

    .sidebar-menu-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #8c7e6c;
        padding: 18px 25px 6px;
        letter-spacing: 0.8px;
    }

    .sidebar-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-nav li a {
        display: flex;
        align-items: center;
        padding: 11px 25px;
        color: #3b3531;
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
    }

    .sidebar-nav li a:hover {
        background: #eadecb;
        color: #1a1a1a;
    }

    .sidebar-nav li a.active {
        background: #e0d3bd;
        color: #1a1a1a;
        border-left: 4px solid #b38e46;
    }

    .sidebar-nav li a i {
        width: 25px;
        font-size: 15px;
        color: #6b5f52;
        transition: color 0.2s;
    }

    .sidebar-nav li a:hover i,
    .sidebar-nav li a.active i {
        color: #b38e46;
    }
</style>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="fas fa-kaaba"></i>
        </div>
        <h4>KEMENHAJ PANEL</h4>
        <p>Sistem Informasi Haji & Umroh</p>
    </div>

    <ul class="sidebar-nav">
        <!-- MENU UTAMA -->
        <li class="sidebar-menu-title">MENU UTAMA</li>
        <li>
            <a href="index.php" class="<?= isActive('index.php', $current_page); ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>

        <!-- MENU UNTUK ADMIN & PETUGAS -->
        <?php if ($role === 'admin' || $role === 'petugas'): ?>
            <li class="sidebar-menu-title">MANAJEMEN DATA</li>
            <li>
                <a href="tabel_jamaah.php" class="<?= isActive('tabel_jamaah.php', $current_page); ?>">
                    <i class="fas fa-users"></i> Data Jamaah
                </a>
            </li>

            <?php if ($role === 'admin'): ?>
                <li>
                    <a href="tabel_user.php" class="<?= isActive(['tabel_user.php', 'form_update_user.php'], $current_page); ?>">
                        <i class="fas fa-user-shield"></i> Kelola Petugas
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="profile_petugas.php" class="<?= isActive('profile_petugas.php', $current_page); ?>">
                        <i class="fas fa-cog"></i> Pengaturan Profil
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a href="tabel_paket.php" class="<?= isActive(['tabel_paket.php', 'form_tambah_paket.php', 'form_update_paket.php'], $current_page); ?>">
                    <i class="fas fa-box"></i> Paket Haji/Umroh
                </a>
            </li>
            <li>
                <a href="tabel_pendaftaran.php" class="<?= isActive(['tabel_pendaftaran.php', 'form_tambah_jadwal.php', 'form_update_pendaftaran.php'], $current_page); ?>">
                    <i class="fas fa-clipboard-list"></i> Pendaftaran
                </a>
            </li>

            <li class="sidebar-menu-title">TRANSAKSI & LAPORAN</li>
            <li>
                <a href="tabel_pembayaran.php" class="<?= isActive('tabel_pembayaran.php', $current_page); ?>">
                    <i class="fas fa-wallet"></i> Pembayaran
                </a>
            </li>
            <li>
                <a href="tabel_keberangkatan.php" class="<?= isActive('tabel_keberangkatan.php', $current_page); ?>">
                    <i class="fas fa-calendar-alt"></i> Jadwal Keberangkatan
                </a>
            </li>
            <li>
                <a href="tabel_laporan.php" class="<?= isActive('tabel_laporan.php', $current_page); ?>">
                    <i class="fas fa-print"></i> Generate Laporan
                </a>
            </li>

            <!-- MENU KHUSUS JAMAAH -->
        <?php elseif ($role === 'jamaah'): ?>
            <li class="sidebar-menu-title">LAYANAN JAMAAH</li>
            <li>
                <a href="riwayat_pendaftaran.php" class="<?= isActive('riwayat_pendaftaran.php', $current_page); ?>">
                    <i class="fas fa-history"></i> Pendaftaran
                </a>
            </li>
            <li>
                <a href="riwayat_pembayaran.php" class="<?= isActive('riwayat_pembayaran.php', $current_page); ?>">
                    <i class="fas fa-receipt"></i> Status Pembayaran
                </a>
            </li>
            <li>
                <a href="jadwal_jamaah.php" class="<?= isActive('jadwal_jamaah.php', $current_page); ?>">
                    <i class="fas fa-calendar-check"></i> Jadwal
                </a>
            </li>

            <li class="sidebar-menu-title">PENGATURAN</li>
            <li>
                <a href="profile_jamaah.php" class="<?= isActive('profile_jamaah.php', $current_page); ?>">
                    <i class="fas fa-user-cog"></i> Profil
                </a>
            </li>
  

        <!-- NAVIGASI SISTEM -->
        <li class="sidebar-menu-title">SISTEM</li>
        <li>
            <a href="../index.php">
                <i class="fas fa-angle-left"></i> Halaman Utama
            </a>
          <?php endif; ?>
        </li>
        <li>
            <a href="logout.php" onclick="return confirm('Yakin ingin logout?');" class="text-danger">
                <i class="fas fa-sign-out-alt text-danger"></i> Logout
            </a>
        </li>
    </ul>
</aside>