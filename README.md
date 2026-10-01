# 🔐 Laporan Pentest — Travel Haji & Umroh

Repositori ini berisi laporan **Penetration Testing** terhadap aplikasi **Travel Haji & Umroh** (PHP + MySQL) yang berjalan di lab **Laragon (Windows)**. Pengujian dilakukan secara **black-box → white-box** setelah berhasil merecovery source code via eksploitasi folder `.git` yang terekspos.

> ⚠️ **Disclaimer:** Seluruh pengujian hanya dilakukan pada lingkungan lab yang sengaja dibuat rentan untuk keperluan pembelajaran / authorized pentest. Penggunaan tanpa izin adalah **ilegal**.

## 📋 Sekilas

| Item | Detail |
| --- | --- |
| **Target** | `http://192.168.100.247/UK-PKL_Banjar/UK1/UK1-Rinjani/` |
| **Periode** | 01 Oktober 2026 |
| **Tester** | gh05t4n |
| **Metodologi** | Black-box → White-box |
| **Total Temuan** | **6** (2 Critical, 1 High, 1 Medium-High, 2 Medium) |

### Level Kerentanan

| Level | Jumlah | Keterangan |
| --- | --- | --- |
| 🔴 Critical | 2 | Bisa langsung dikuasai penyerang |
| 🟠 High | 1 | Bocorkan informasi penting server |
| 🟡 Medium-High | 1 | Bocorkan struktur database |
| 🟡 Medium | 2 | Validasi input lemah |
| 🔵 Low | 0 | — |

## 📂 Struktur Repositori

```
.
├── README.md
├── Laporan/
│   └── Laporan.md          ← Laporan lengkap
└── Temuan/
    ├── *.txt               ← Hasil recon & tools
    ├── travel_haji_umroh.sql
    ├── gitLog*.txt
    ├── feroxbuster*.txt
    └── hasil-git/          ← Source code hasil git-dumper
```

## 📖 Baca Laporan Lengkap

Detail temuan, proof of concept, bukti, dan rekomendasi perbaikan tersedia di:

👉 **[`Laporan/Laporan.md`](Laporan/Laporan.md)**

---

**Author:** gh05t4n · **Tanggal:** 01 Okt 2026