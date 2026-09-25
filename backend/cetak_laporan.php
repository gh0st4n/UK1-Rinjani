<?php
/*
    backend/cetak_laporan.php
    ---------------------------
    Halaman laporan versi cetak - sengaja dibuat SIMPEL (bukan
    tampilan modern kayak dashboard), supaya rapi & jelas kalau
    diprint atau di-export ke PDF. Query datanya sama dengan
    tabel_laporan.php.
*/

include "database/connection.php";
require_once "classes/Auth.php";

$auth = new Auth((new Database())->getConnection());
$auth->checkRole(['admin', 'petugas']);

$conn = mysqli_connect("localhost", "root", "", "travel_haji_umroh");

$totalPendaftaran = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pendaftaran");
$dataPendaftar = mysqli_fetch_assoc($totalPendaftaran);

$totalLunas = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pembayaran WHERE status = 'Valid'");
$dataLunas = mysqli_fetch_assoc($totalLunas);

$totalPembayaran = mysqli_query($conn, "SELECT SUM(nominal) AS totalBayar FROM pembayaran");
$dataPembayaran = mysqli_fetch_assoc($totalPembayaran);

$laporan = mysqli_query($conn, "SELECT jamaah.nama_lengkap, paket.nama_paket, pendaftaran.tgl_daftar, paket.harga, pembayaran.nominal, pembayaran.status
FROM pendaftaran JOIN paket ON pendaftaran.paket_id = paket.id JOIN jamaah ON pendaftaran.jamaah_id LEFT JOIN pembayaran ON jamaah.id = pembayaran.jamaah_id");

$totalHargaKeseluruhan = 0;
$totalDibayarKeseluruhan = 0;
$rows = [];
while ($d = mysqli_fetch_assoc($laporan)) {
    $rows[] = $d;
    $totalHargaKeseluruhan   += (float) $d['harga'];
    $totalDibayarKeseluruhan += (float) $d['nominal'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi - Kemenhaj Panel</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #111;
            margin: 30px;
        }

        .kop {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .kop h1 {
            font-size: 18px;
            margin: 0 0 4px 0;
        }

        .kop p {
            margin: 0;
            font-size: 12px;
        }

        .judul-laporan {
            text-align: center;
            margin-bottom: 18px;
        }

        .judul-laporan h2 {
            font-size: 15px;
            text-decoration: underline;
            margin: 0 0 4px 0;
        }

        .judul-laporan span {
            font-size: 12px;
            color: #444;
        }

        table.ringkasan {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        table.ringkasan td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 12px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.data th,
        table.data td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 12px;
        }

        table.data th {
            background-color: #e5e5e5;
            text-align: center;
        }

        table.data td.angka {
            text-align: right;
        }

        table.data td.tengah {
            text-align: center;
        }

        tfoot td {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .ttd {
            margin-top: 50px;
            width: 100%;
        }

        .ttd td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 12px;
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .no-print button {
            padding: 8px 20px;
            font-size: 14px;
            cursor: pointer;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 10px;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <a href="tabel_laporan.php" style="padding: 8px 20px; font-size: 14px; text-decoration: none; border: 1px solid #333; color: #111; border-radius: 4px; margin-right: 10px; display: inline-block;">&larr; Kembali ke Panel</a>
        <button onclick="window.print()">Cetak / Simpan sebagai PDF</button>
    </div>

    <div class="kop">
        <h1>KEMENHAJ PANEL</h1>
        <p>Sistem Informasi Haji &amp; Umroh</p>
    </div>

    <div class="judul-laporan">
        <h2>LAPORAN REKAPITULASI PENDAFTARAN &amp; PEMBAYARAN JAMAAH</h2>
        <span>Dicetak pada: <?php echo date('d F Y, H:i'); ?> WIB</span>
    </div>

    <table class="ringkasan">
        <tr>
            <td><strong>Total Pendaftaran</strong></td>
            <td><?php echo (int) $dataPendaftar['total']; ?> Jamaah</td>
            <td><strong>Pendaftaran Lunas</strong></td>
            <td><?php echo (int) $dataLunas['total']; ?> Jamaah</td>
            <td><strong>Total Pembayaran Masuk</strong></td>
            <td>Rp <?php echo number_format((float) $dataPembayaran['totalBayar'], 0, ',', '.'); ?></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Nama Jamaah</th>
                <th>Paket Terpilih</th>
                <th>Tgl Daftar</th>
                <th>Total Harga</th>
                <th>Total Dibayar</th>
                <th>Status Bayar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)) { ?>
                <tr>
                    <td colspan="7" class="tengah">Belum ada data.</td>
                </tr>
                <?php } else {
                $no = 1;
                foreach ($rows as $d) { ?>
                    <tr>
                        <td class="tengah"><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($d['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($d['nama_paket']); ?></td>
                        <td class="tengah"><?php echo htmlspecialchars($d['tgl_daftar']); ?></td>
                        <td class="angka">Rp <?php echo number_format((float) $d['harga'], 0, ',', '.'); ?></td>
                        <td class="angka">Rp <?php echo number_format((float) $d['nominal'], 0, ',', '.'); ?></td>
                        <td class="tengah"><?php echo htmlspecialchars($d['status'] ?? '-'); ?></td>
                    </tr>
            <?php }
            } ?>
        </tbody>
        <?php if (!empty($rows)) { ?>
            <tfoot>
                <tr>
                    <td colspan="4" class="tengah">TOTAL</td>
                    <td class="angka">Rp <?php echo number_format($totalHargaKeseluruhan, 0, ',', '.'); ?></td>
                    <td class="angka">Rp <?php echo number_format($totalDibayarKeseluruhan, 0, ',', '.'); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        <?php } ?>
    </table>

    <table class="ttd">
        <tr>
            <td>
                Mengetahui,<br><br><br><br>
                ( .................................... )<br>
                Petugas
            </td>
            <td>
                <?php echo date('d F Y'); ?><br><br><br><br>
                ( .................................... )<br>
                Admin
            </td>
        </tr>
    </table>

</body>

</html>