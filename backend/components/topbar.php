<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = strtolower($_SESSION['role'] ?? '');

if ($role === 'admin') {
    $panelTitle = 'Panel Kelola Admin';
    $panelIcon  = 'fa-building';
} elseif ($role === 'petugas') {
    $panelTitle = 'Panel Kelola Petugas';
    $panelIcon  = 'fa-user-shield';
} else {
    $panelTitle = 'Panel Layanan Jamaah';
    $panelIcon  = 'fa-kaaba';
}

$avatarIcon = ($role === 'jamaah') ? 'fa-user' : 'fa-user-tie';
?>

<div class="topbar d-flex justify-content-between align-items-center px-3 px-md-4 py-3 border-bottom w-100 shadow-sm" style="background-color: #faf7f2; border-color: #e0d6c5 !important;">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light d-lg-none border-0 p-2" id="btnToggleSidebar" type="button" style="background-color: #eae5dc;">
            <i class="fas fa-bars fa-lg" style="color: #1a1a1a;"></i>
        </button>

        <div class="fw-bold d-flex align-items-center" style="color: #1a1a1a;">
            <i class="fas <?= $panelIcon; ?> me-2" style="color: #b38e46;"></i>
            <span class="d-none d-sm-inline"><?= $panelTitle; ?></span>
            <span class="d-inline d-sm-none">Kemenhaj</span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 gap-md-3">
        <div class="text-end">
            <div class="fw-bold small" style="color: #1a1a1a;"><?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
            <span class="badge border fw-semibold" style="font-size: 10px; background-color: #e8decb; color: #4a3b18; border-color: #d4c5ab !important;">
                <?= strtoupper($_SESSION['role'] ?? 'GUEST'); ?>
            </span>
        </div>
        <div class="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; background-color: #1a1a1a; border: 1.5px solid #c5a059;">
            <i class="fas <?= $avatarIcon; ?>" style="color: #c5a059;"></i>
        </div>
    </div>
</div>