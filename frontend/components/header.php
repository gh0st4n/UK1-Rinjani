<?php
// Path dasar, disesuaikan tergantung dari mana header ini dipanggil:
// - index.php (root)       -> $baseRoot = '', $basePages = 'frontend/pages/'
// - frontend/pages/*.php   -> $baseRoot = '../../', $basePages = ''
$baseRoot   = $baseRoot ?? '';
$basePages  = $basePages ?? 'frontend/pages/';
$activePage = $activePage ?? 'home'; // 'home' | 'tentang' | 'paket' | 'paket-anda'

// Tentukan tautan menu berdasar konteks lokasi file
$hrefHome    = ($baseRoot === '') ? '#hero'     : $baseRoot . 'index.php#hero';
$hrefTentang = ($baseRoot === '') ? '#about'    : $baseRoot . 'index.php#about';
$hrefPaket   = ($baseRoot === '') ? '#services' : $baseRoot . 'index.php#services';

// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proses logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: " . $baseRoot . "index.php");
    exit;
}

$isLoggedIn  = isset($_SESSION['login']) && $_SESSION['login'] === true;
$displayName = $_SESSION['username'] ?? '';
$userRole    = $_SESSION['role'] ?? '';

// Cek apakah jamaah sudah memiliki pendaftaran paket
$hasPendaftaran = false;
if ($isLoggedIn && $userRole === 'jamaah') {
    require_once __DIR__ . '/../database/connection.php';
    try {
        $db = (new Database())->getConnection();
        $stmtCek = $db->prepare(
            "SELECT COUNT(*) FROM pendaftaran p
             LEFT JOIN jamaah j ON p.jamaah_id = j.id
             WHERE j.user_id = :user_id"
        );
        $stmtCek->execute([':user_id' => $_SESSION['user_id'] ?? 0]);
        $hasPendaftaran = ((int) $stmtCek->fetchColumn()) > 0;
    } catch (PDOException $e) {
        $hasPendaftaran = false;
    }
}
?>

<header id="header" class="header d-flex align-items-center fixed-top">
  <div class="container-fluid container-xl position-relative d-flex align-items-center">

    <a href="<?= $baseRoot ?>index.php" class="logo d-flex align-items-center me-auto">
      <!-- <img src="assets/img/logo.webp" alt=""> -->
      <h1 class="sitename">TRAVEL HAJI & UMROH</h1>
    </a>

    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="<?= $hrefHome ?>" class="<?= $activePage === 'home' ? 'active' : '' ?>">Home</a></li>
        <li><a href="<?= $hrefTentang ?>" class="<?= $activePage === 'tentang' ? 'active' : '' ?>">Tentang</a></li>
        <li><a href="<?= $hrefPaket ?>" class="<?= $activePage === 'paket' ? 'active' : '' ?>">Data Paket</a></li>
        <?php if ($hasPendaftaran): ?>
          <li><a href="<?= $basePages ?>riwayat.php" class="<?= $activePage === 'paket-anda' ? 'active' : '' ?>">Paket Anda</a></li>
        <?php endif; ?>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>

    <?php if ($isLoggedIn): ?>
      <div class="d-flex align-items-center gap-2 auth-area">
        <a href="<?= $baseRoot ?>backend/index.php" class="btn-register d-flex align-items-center gap-1 text-decoration-none">
          <i class="bi bi-person-circle"></i>
          <span><?= htmlspecialchars($displayName) ?></span>
        </a>
        <a class="btn-register" href="<?= $baseRoot ?>index.php?logout=1" onclick="return confirm('Yakin ingin logout?');">Logout</a>
      </div>
    <?php else: ?>
      <div class="d-flex align-items-center gap-2 auth-area">
        <a class="btn-register" href="<?= $basePages ?>register.php">Register</a>
        <a class="btn-register" href="<?= $basePages ?>login.php">Login</a>
      </div>
    <?php endif; ?>

  </div>
</header>

<style>
  .auth-area {
    margin-left: 20px;
  }
  .btn-register {
    background: transparent;
    color: #fff;
    border: 2px solid rgba(255, 255, 255, 0.6);
    padding: 8px 22px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: 0.3s;
    white-space: nowrap;
  }
  .btn-register:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: #fff;
    color: #fff;
  }
  #header .navmenu .active,
  #header .navmenu .active:focus {
    color: #d4af37 !important;
  }
</style>