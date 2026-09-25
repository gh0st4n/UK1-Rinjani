<?php
/*
    frontend/pages/riwayat.php
    -----------------------------
    Halaman "Paket Anda" di frontend. Datanya sama sumbernya dengan
    backend/jadwal_jamaah.php (jadwal keberangkatan tiap jamaah),
    tapi tampilannya SENGAJA dibuat beda dari backend: kartu
    timeline hijau-emas ala web depan, bukan tabel admin panel.
*/

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../database/connection.php';

$isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;
$userRole   = $_SESSION['role'] ?? '';

if (!$isLoggedIn) {
  header("Location: login.php?redirect=" . urlencode('riwayat.php'));
  exit;
}

$db = (new Database())->getConnection();

$userId   = $_SESSION['user_id'] ?? null;
$jamaahId = $_SESSION['jamaah_id'] ?? null;

$jadwalList = [];
$successMessage = "";

if (isset($_GET['status']) && $_GET['status'] === 'success') {
  $successMessage = "Pendaftaran berhasil diajukan! Silakan tunggu verifikasi admin.";
}

if ($userRole === 'jamaah') {
  try {
    $query = "SELECT p.id AS pendaftaran_id, p.status, p.tgl_daftar,
                         k.*,
                         pk.nama_paket, pk.jenis AS jenis_layanan, pk.durasi,
                         COALESCE(pk.harga, 0) AS harga_paket
                  FROM pendaftaran p
                  INNER JOIN paket pk ON p.paket_id = pk.id
                  LEFT JOIN keberangkatan k ON p.keberangkatan_id = k.id";

    $params = [];
    if (!empty($jamaahId)) {
      $query .= " WHERE p.jamaah_id = :jamaah_id";
      $params[':jamaah_id'] = $jamaahId;
    } else if (!empty($userId)) {
      $query .= " LEFT JOIN jamaah j ON p.jamaah_id = j.id WHERE j.user_id = :user_id";
      $params[':user_id'] = $userId;
    }
    $query .= " ORDER BY p.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $jadwalList = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $jadwalList = [];
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Paket Anda - Rinn</title>

  <link href="../template/assets/img/traveloka.png" rel="icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <link href="../template/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../template/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <link href="../template/assets/css/main.css" rel="stylesheet">

  <style>
    #header {
      background: linear-gradient(135deg, #064e3b 0%, #022c22 100%) !important;
    }

    #header .sitename {
      color: #ffffff !important;
    }

    #header .navmenu ul li a {
      color: #e5e7eb !important;
    }

    #header .navmenu ul li a:hover {
      color: #d4af37 !important;
    }

    #footer {
      background: linear-gradient(135deg, #022c22 0%, #064e3b 100%) !important;
      color: #e5e7eb !important;
    }

    #footer .sitename {
      color: #ffffff !important;
    }

    #footer .copyright {
      background: #022c22 !important;
      color: #e5e7eb !important;
    }

    .jadwal-card {
      background: #fff;
      border-radius: 22px;
      padding: 26px 28px;
      margin-bottom: 22px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(6, 78, 59, 0.06);
      border-left: 5px solid #d97706;
    }

    .jadwal-card.status-mendatang {
      border-left-color: #0284c7;
    }

    .jadwal-card.status-berlangsung {
      border-left-color: #047857;
    }

    .jadwal-card.status-selesai {
      border-left-color: #94a3b8;
    }

    .jadwal-kaaba-icon {
      width: 54px;
      height: 54px;
      border-radius: 16px;
      background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 1.3rem;
      flex-shrink: 0;
    }

    .jenis-pill {
      display: inline-block;
      font-size: 0.72rem;
      font-weight: 700;
      padding: 3px 12px;
      border-radius: 999px;
      background: #fef3c7;
      color: #b45309;
      text-transform: uppercase;
      letter-spacing: .04em;
    }

    .flight-route {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 14px;
      font-size: 0.92rem;
      color: #334155;
    }

    .flight-route .dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #d97706;
    }

    .flight-route .line {
      flex: 1;
      height: 2px;
      background: repeating-linear-gradient(90deg, #cbd5e1 0 6px, transparent 6px 12px);
      position: relative;
    }

    .flight-route .line i {
      position: absolute;
      top: -9px;
      left: 50%;
      transform: translateX(-50%);
      color: #d97706;
      background: #fff;
      padding: 0 4px;
    }

    .status-pill {
      font-size: 0.75rem;
      font-weight: 700;
      padding: 6px 16px;
      border-radius: 999px;
    }
  </style>
</head>

<body class="index-page">

  <?php $baseRoot = '../../';
  $basePages = '';
  $activePage = 'paket-anda';
  include '../components/header.php' ?>

  <main class="main">
    <section class="section" style="padding-top: 140px; padding-bottom: 100px; background:#f8fafc; min-height: 80vh;">
      <div class="container">

        <div class="mb-4">
          <span class="badge px-3 py-2 rounded-pill mb-2" style="background:#d1fae5; color:#047857; font-weight:700;">
            <i class="fas fa-kaaba me-1"></i> Paket Anda
          </span>
          <h3 class="fw-bold mb-1" style="color:#064e3b;">Perjalanan Ibadah Anda</h3>
          <p class="text-muted mb-0">Pantau paket dan jadwal keberangkatan yang sudah Anda daftarkan.</p>
        </div>

        <?php if ($successMessage) { ?>
          <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
            <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($successMessage); ?>
          </div>
        <?php } ?>

        <?php if ($userRole !== 'jamaah') { ?>

          <p class="text-muted">Halaman ini cuma tersedia untuk akun jamaah.</p>

        <?php } else if (count($jadwalList) === 0) { ?>

          <div class="text-center py-5">
            <i class="fas fa-kaaba fa-3x mb-3 d-block" style="color:#d4af37; opacity:.6;"></i>
            <p class="text-muted">Anda belum memiliki paket yang didaftarkan.</p>
            <a href="../../index.php#services" class="btn mt-2" style="background:linear-gradient(135deg, #d97706 0%, #b45309 100%); color:#fff; font-weight:700;">
              Lihat Data Paket
            </a>
          </div>

        <?php } else { ?>

          <?php foreach ($jadwalList as $row) {
            $tglBerangkat = $row['tanggal_berangkat'] ?? null;
            $tglPulang    = $row['tgl_kepulangan'] ?? null;
            $today        = date('Y-m-d');

            if ($tglBerangkat && $tglBerangkat > $today) {
              $statusClass = 'status-mendatang';
              $statusStyle = 'background:#e0f2fe; color:#0284c7;';
              $statusIcon  = 'fa-plane-departure';
              $statusText  = 'Mendatang';
            } else if ($tglBerangkat && $tglBerangkat <= $today && (!$tglPulang || $tglPulang >= $today)) {
              $statusClass = 'status-berlangsung';
              $statusStyle = 'background:#d1fae5; color:#047857;';
              $statusIcon  = 'fa-sync-alt';
              $statusText  = 'Berlangsung';
            } else if ($tglBerangkat) {
              $statusClass = 'status-selesai';
              $statusStyle = 'background:#f1f5f9; color:#64748b;';
              $statusIcon  = 'fa-check-circle';
              $statusText  = 'Selesai';
            } else {
              $statusClass = '';
              $statusStyle = 'background:#fff7ed; color:#ea580c;';
              $statusIcon  = 'fa-clock';
              $statusText  = 'Menunggu Jadwal';
            }
          ?>

            <div class="jadwal-card <?php echo $statusClass; ?>">
              <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div class="d-flex gap-3">
                  <div class="jadwal-kaaba-icon">
                    <i class="fas fa-kaaba"></i>
                  </div>
                  <div>
                    <span class="jenis-pill"><?php echo htmlspecialchars($row['jenis_layanan'] ?? 'Haji/Umroh'); ?></span>
                    <h5 class="fw-bold mt-2 mb-0" style="color:#064e3b;"><?php echo htmlspecialchars($row['nama_paket'] ?? 'Layanan'); ?></h5>
                    <div class="text-muted small mt-1">
                      <i class="far fa-calendar-alt me-1"></i> Didaftarkan <?php echo isset($row['tgl_daftar']) ? date('d M Y', strtotime($row['tgl_daftar'])) : '-'; ?>
                      &middot; <i class="fas fa-plane ms-1 me-1"></i><?php echo htmlspecialchars($row['maskapai'] ?? 'Maskapai belum ditentukan'); ?>
                    </div>
                  </div>
                </div>
                <span class="status-pill" style="<?php echo $statusStyle; ?>">
                  <i class="fas <?php echo $statusIcon; ?> me-1"></i><?php echo $statusText; ?>
                </span>
              </div>

              <div class="flight-route">
                <div class="dot"></div>
                <div class="line"><i class="fas fa-plane"></i></div>
                <div class="dot"></div>
              </div>
              <div class="d-flex justify-content-between small text-muted mt-1">
                <div>
                  <div class="fw-bold text-dark"><?php echo $tglBerangkat ? date('d M Y', strtotime($tglBerangkat)) : 'Menunggu jadwal'; ?></div>
                  Berangkat &middot; <?php echo htmlspecialchars($row['embarkasi'] ?? '-'); ?>
                </div>
                <div class="text-end">
                  <div class="fw-bold text-dark"><?php echo $tglPulang ? date('d M Y', strtotime($tglPulang)) : '-'; ?></div>
                  Kepulangan
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <div class="fw-bold" style="color:#d97706;">Rp <?php echo number_format($row['harga_paket'], 0, ',', '.'); ?></div>
                <button type="button" class="btn btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalJadwal<?php echo $row['pendaftaran_id']; ?>" style="background:#f1f5f9; color:#064e3b; font-weight:700; border-radius:10px;">
                  Lihat Detail Perjalanan <i class="bi bi-arrow-right ms-1"></i>
                </button>
              </div>
            </div>

            <!-- Modal Detail -->
            <div class="modal fade" id="modalJadwal<?php echo $row['pendaftaran_id']; ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow p-2">
                  <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color:#064e3b;"><i class="fas fa-plane-departure me-2" style="color:#d97706;"></i>Detail Perjalanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body pt-3">
                    <div class="row g-3">
                      <div class="col-6">
                        <div class="text-muted small">Nama Paket</div>
                        <div class="fw-bold" style="color:#064e3b;"><?php echo htmlspecialchars($row['nama_paket'] ?? '-'); ?></div>
                      </div>
                      <div class="col-6">
                        <div class="text-muted small">Maskapai</div>
                        <div class="fw-bold"><?php echo htmlspecialchars($row['maskapai'] ?? '-'); ?></div>
                      </div>
                      <div class="col-6">
                        <div class="text-muted small">Tanggal Berangkat</div>
                        <div class="fw-bold text-success"><?php echo $tglBerangkat ? date('d M Y', strtotime($tglBerangkat)) : '-'; ?></div>
                      </div>
                      <div class="col-6">
                        <div class="text-muted small">Tanggal Kepulangan</div>
                        <div class="fw-bold"><?php echo $tglPulang ? date('d M Y', strtotime($tglPulang)) : '-'; ?></div>
                      </div>
                      <div class="col-6">
                        <div class="text-muted small">Embarkasi</div>
                        <div class="fw-bold"><?php echo htmlspecialchars($row['embarkasi'] ?? '-'); ?></div>
                      </div>
                      <div class="col-6">
                        <div class="text-muted small">Durasi</div>
                        <div class="fw-bold"><?php echo !empty($row['durasi']) ? htmlspecialchars($row['durasi']) . ' Hari' : '-'; ?></div>
                      </div>
                    </div>
                    <?php if (!empty($row['keterangan'])) { ?>
                      <div class="mt-3">
                        <div class="text-muted small mb-1">Catatan</div>
                        <div class="p-3 rounded-3" style="background:#f8fafc;"><?php echo nl2br(htmlspecialchars($row['keterangan'])); ?></div>
                      </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </div>

          <?php } ?>

        <?php } ?>

      </div>
    </section>
  </main>

  <footer id="footer" class="footer">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="../../index.php" class="d-flex align-items-center">
            <span class="sitename">Arsha</span>
          </a>
        </div>
      </div>
      <div class="container copyright text-center mt-4">
        <p>&copy; <span>Copyright</span> <strong class="px-1 sitename">Arsha</strong> <span>All Rights Reserved</span></p>
      </div>
    </div>
  </footer>

  <script src="../template/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../template/assets/js/main.js"></script>

</body>

</html>