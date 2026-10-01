# LAPORAN PENTEST — Aplikasi Travel Haji & Umroh

- **Target:** `http://192.168.100.247/UK-PKL_Banjar/UK1/UK1-Rinjani/`
- **Setup Lab:** Aplikasi berjalan di **Windows (Laragon)**, Attacker di **Kali Linux**
- **Tanggal:** 28 September – 01 Oktober 2026
- **Tester:** gh05t4n
- **Metodologi:** Black-box → White-box (setelah recovery source code via `.git`)

## Daftar Isi

- [LAPORAN PENTEST — Aplikasi Travel Haji \& Umroh](#laporan-pentest--aplikasi-travel-haji--umroh)
  - [Daftar Isi](#daftar-isi)
  - [1. Ringkasan Eksekutif](#1-ringkasan-eksekutif)
  - [2. Lingkup \& Setup Lab](#2-lingkup--setup-lab)
  - [3. Metodologi \& Reconnaissance](#3-metodologi--reconnaissance)
    - [Tools](#tools)
    - [Struktur Folder](#struktur-folder)
    - [Reconnaissance](#reconnaissance)
  - [4. Temuan](#4-temuan)
    - [4.1 Git Repository Exposure (CRITICAL)](#41-git-repository-exposure-critical)
    - [4.2 Credential Leak di Git History (CRITICAL)](#42-credential-leak-di-git-history-critical)
    - [4.3 Directory Listing Aktif (MEDIUM)](#43-directory-listing-aktif-medium)
    - [4.4 Full Path Disclosure via PHP Error (HIGH)](#44-full-path-disclosure-via-php-error-high)
    - [4.5 Business Logic — Validasi No. WhatsApp Lemah (MEDIUM)](#45-business-logic--validasi-no-whatsapp-lemah-medium)
    - [4.6 Information Disclosure via SQL Error (MEDIUM-HIGH)](#46-information-disclosure-via-sql-error-medium-high)
  - [5. Vektor yang Diuji \& Aman](#5-vektor-yang-diuji--aman)
  - [6. Matriks Risiko](#6-matriks-risiko)
  - [7. Rekomendasi Perbaikan](#7-rekomendasi-perbaikan)
    - [Prioritas 1 (Immediate)](#prioritas-1-immediate)
    - [Prioritas 2 (Short-term)](#prioritas-2-short-term)
    - [Prioritas 3 (Long-term)](#prioritas-3-long-term)
  - [8. Panduan Aman Push ke GitHub](#8-panduan-aman-push-ke-github)
    - [8.1 Buat `.gitignore` yang Proper](#81-buat-gitignore-yang-proper)
    - [8.2 Gunakan Environment Variable](#82-gunakan-environment-variable)
    - [8.3 Scan Credential Sebelum Push](#83-scan-credential-sebelum-push)
    - [8.4 Pre-commit Hook](#84-pre-commit-hook)
    - [8.5 Kalau Sudah Terlanjur Commit — Bersihkan History](#85-kalau-sudah-terlanjur-commit--bersihkan-history)
    - [8.6 Checklist Sebelum Push](#86-checklist-sebelum-push)
  - [9. Lampiran](#9-lampiran)
    - [A. Struktur Folder Hasil Git Dump](#a-struktur-folder-hasil-git-dump)
    - [B. Command yang Digunakan](#b-command-yang-digunakan)
    - [C. Timeline](#c-timeline)
    - [D. Referensi](#d-referensi)

## 1. Ringkasan Eksekutif

Aplikasi **Travel Haji & Umroh** memiliki **2 temuan Critical**, **1 High**, **1 Medium-High**, dan **3 Medium**. Temuan paling berdampak adalah **eksposur folder `.git`** yang memungkinkan penyerang mengambil **seluruh source code** dan **credential admin** dari **git history**.

Kombinasi `.git` bocor dengan **kredensial admin plaintext** di file `travel_haji_umroh.sql` memungkinkan penyerang **login sebagai admin** tanpa perlu crack password. Dari sana, penyerang memiliki **full access** ke seluruh fungsi aplikasi.

Selain itu, ditemukan **beberapa kerentanan pada validasi input** (NIK, No. WhatsApp), **directory listing** di folder `backend/process/`, dan **full path disclosure** via PHP error yang membocorkan struktur direktori server.

> **Status perbaikan:** Semua temuan **belum dipatch** — masih butuh perubahan di level konfigurasi server dan validasi input.

**Catatan penting:** Setup lab ini **sengaja** dibuat rentan untuk keperluan pembelajaran. Di lingkungan production, konfigurasi seperti ini **tidak boleh** terjadi.

**Prioritas perbaikan:**

1. **Hapus folder `.git`** dari server production
2. **Hash semua password** dengan bcrypt (bukan plaintext)
3. Set `display_errors = Off` di production
4. Nonaktifkan directory listing
5. Validasi input NIK & No. WhatsApp server-side
6. Tangkap exception dengan pesan generik

## 2. Lingkup & Setup Lab

| Komponen       | Detail                                                   |
| -------------- | -------------------------------------------------------- |
| **Aplikasi**   | Travel Haji & Umroh (PHP + MySQL)                        |
| **Web Server** | Laragon (Windows)                                        |
| **Target IP**  | `192.168.100.247`                                        |
| **Attacker**   | Kali Linux (VM)                                          |
| **Jaringan**   | Bridged / Host-Only (satu subnet `192.168.100.x`)        |
| **Scope**      | `http://192.168.100.247/UK-PKL_Banjar/UK1/UK1-Rinjani/`  |

## 3. Metodologi & Reconnaissance

### Tools

- `feroxbuster` — directory brute-force
- `git-dumper` — recovery `.git`
- `hashcat` — crack hash password
- `curl` / browser — manual testing
- `sqlmap` — SQLi testing (belum dijalankan)

### Struktur Folder

```
UK1-Rinjani/
├── backend/
│   ├── classes/           ← Auth, Paket, User, Pendaftaran
│   ├── components/        ← header, sidebar, topbar, footer, bottom
│   ├── database/          ← connection.php
│   ├── process/           ← proses_delete_jadwal, proses_hapus_user
│   ├── uploads/           ← folder upload (belum ada)
│   └── [40+ file .php]    ← form, tabel, proses
├── frontend/
│   ├── components/        ← header, footer
│   ├── database/          ← connection.php
│   ├── pages/             ← about, hero, login, register, dll
│   ├── partials/          ← head, script, scroll-top
│   └── template/          ← assets, CSS, JS, vendor
└── index.php
```

### Reconnaissance

```bash
feroxbuster -u http://192.168.100.247/UK-PKL_Banjar/UK1/UK1-Rinjani/ \
  -w /usr/share/wordlists/dirb/common.txt
```

**Hasil menarik:**

```
200  .git/HEAD                          → Git exposed
301  backend/                           → Directory listing
301  backend/process/                   → Directory listing
301  frontend/                          → Directory listing
200  backend/database/connection.php    → Config DB (0 bytes saat diakses langsung)
200  backend/classes/Auth.php           → Logika auth
200  frontend/pages/proses-pendaftaran.php
```

## 4. Temuan

### 4.1 Git Repository Exposure (CRITICAL)

**Deskripsi:**
Folder `.git` dapat diakses publik via HTTP, memungkinkan recovery source code lengkap. Laragon (default) tidak memblokir akses ke `.git`.

**URL:** `http://192.168.100.247/UK-PKL_Banjar/UK1/UK1-Rinjani/.git/`

**Proof of Concept:**

```bash
git-dumper http://192.168.100.247/UK-PKL_Banjar/UK1/UK1-Rinjani/.git/ ./hasil-git

[-] Testing http://192.168.100.247/UK-PKL_Banjar/UK1/UK1-Rinjani/.git/HEAD [200]
[-] Testing http://192.168.100.247/UK-PKL_Banjar/UK1/UK1-Rinjani/.git/ [200]
[-] Fetching .git recursively
...
[-] Running git checkout .
Updated 204 paths from the index
```

**Hasil:**

- **204 file** source code ter-recover
- Termasuk `backend/database/connection.php`, `backend/classes/Auth.php`
- Commit history lengkap dari `first commit` sampai `Push Gabut`

**Commit History:**

```
109abf39 (HEAD -> main) Push Gabut
37417a37 anggap aja selesai
ab5a9959 anggap aja selesai
a4e2b4d2 Merge pull request #2 from gh0st4n/main
71d9b302 24-09-2026
badcded5 Merge pull request #1 from gh0st4n/main
30dc0863 Nih Tugas nya
5fbe4ae1 progres
054dfeec first commit
```

**Dampak:**

- Source code lengkap terekspos → memudahkan analisis kerentanan
- Git history bisa dibaca → credential & file sensitif yang dihapus masih ada
- File SQL dump (`travel_haji_umroh.sql`) ikut terekspos

**Severity:** 🔴 **CRITICAL** (CVSS 9.1)

**Remediasi:**

```apache
# Laragon / Apache .htaccess
RedirectMatch 404 /\.git
```

Atau di Nginx:

```nginx
location ~ /\.git { deny all; }
```

### 4.2 Credential Leak di Git History (CRITICAL)

**Deskripsi:**
File `travel_haji_umroh.sql` sempat ditambahkan ke repository, lalu **dihapus di commit `109abf39`** ("Push Gabut"). Namun, **file tersebut masih tersimpan di git history** dan bisa diakses via `git show <commit>:travel_haji_umroh.sql`. Di dalamnya terdapat **kredensial admin plaintext**.

**Proof of Concept:**

```bash
# Cari file yang dihapus tapi masih ada di history
git log --all --diff-filter=D --name-only

# Output:
# === 109abf39 Push Gabut ===
# pi-pi
# travel_haji_umroh.sql
# TRAVEL-UMROH2

# Tampilkan isi file dari parent commit
git show 109abf39^:travel_haji_umroh.sql
```

**Isi file (bagian `INSERT INTO user`):**

```sql
INSERT INTO `user` (`id`, `username`, `password`, `role`, `jamaah_id`) VALUES
(1, 'Rinjani', 'rinjanicantik', 'admin', NULL),
(5, 'Zahra', '$2y$10$HzHGL4R/rZ5KAx7AaZSY7uTRW.oFcGC.V9CKlu0ivUtZeaFUZ4XCO', 'petugas', NULL),
(23, 'luthfi', '$2y$10$O1U6U1oMnl0avbf5YUimC.krl0zqMgbT9jp4DBBz5ntUesweasFLy', 'petugas', NULL),
(27, 'yuda aja', '$2y$10$73gXx3d12lG7o6RZTd1eUuOoY/Cr3IRrkeNIlRKkfAzYgtdrrBfxe', 'jamaah', 21),
(29, 'Kamari', '$2y$10$a3SmKwNbBUhtY9FrFEaw7./aGOMM1c4/JXDpyFF0AaQ37Q8CFzvNS', 'jamaah', 23),
(31, 'rasya', '$2y$10$znH7Lrhg1XpCvytLwnoX7uby9LwlUQpwRsZt7RZ/bQi5kFIIbSC2u', 'jamaah', 25),
(32, 'Rajian', '$2y$10$FS.JokNP35BPtDl.J0TUj.HcZilpon782koZZZptKJwFkSExsx/iu', 'jamaah', 26);
```

**Credential yang Didapat:**

| Role       | Username   | Password            | Status          |
| ---------- | ---------- | ------------------- | --------------- |
| **Admin**  | `Rinjani`  | `rinjanicantik`     | ✅ Login berhasil |
| Petugas    | `Zahra`    | *bcrypt, belum crack* | -               |
| Petugas    | `luthfi`   | *bcrypt, belum crack* | -               |
| Jamaah     | `yuda aja` | *bcrypt, belum crack* | -               |

> **Catatan penting:** Password admin `Rinjani` **plaintext** (tidak di-hash). Ini bisa terjadi karena admin di-set manual via phpMyAdmin, bukan via form register yang pakai `password_hash()`.

**Cross-check:** Source code `backend/classes/Auth.php`:

```php
if (password_verify($password, $user['password']) || $password === $user['password']) {
```

Fallback `|| $password === $user['password']` → **plaintext auth berhasil**. ✅

**Eksploitasi:**

```
URL: http://192.168.100.247/UK-PKL_Banjar/UK1/UK1-Rinjani/backend/login.php
Username: Rinjani
Password: rinjanicantik
```

→ **Login berhasil sebagai admin** ✅

**Dampak:**

- Full access ke aplikasi sebagai admin
- CRUD data jamaah, paket, pendaftaran, pembayaran
- Upload file (jika kolom `bukti_transfer` ada)
- Akses laporan keuangan

**Severity:** 🔴 **CRITICAL** (CVSS 9.8)

**Remediasi:**

- Rotasi seluruh password (admin, petugas, jamaah, DB)
- Hash semua password dengan bcrypt
- Hapus git history yang mengandung credential:
  ```bash
  git filter-repo --path travel_haji_umroh.sql --invert-paths
  ```
- Jangan pernah commit credential ke repository

### 4.3 Directory Listing Aktif (MEDIUM)

**Deskripsi:**
Folder `backend/process/` mengaktifkan directory listing, memungkinkan enumerasi file.

**URL:** `http://localhost/UK-PKL_Banjar/UK1/UK1-Rinjani/backend/process/`

**Proof of Concept:**
Akses URL tersebut di browser → muncul halaman "Index of /UK-PKL_Banjar/UK1/UK1-Rinjani/backend/process" dengan daftar file:

- `proses_delete_jadwal.php`
- `proses_hapus_user.php`

**Dampak:**

- Enumerasi file tanpa fuzzing
- Reconnaissance struktur direktori backend
- Attacker tahu endpoint yang bisa diakses langsung

**Severity:** 🟡 **MEDIUM** (CVSS 5.3)

**Remediasi:**

```apache
Options -Indexes
```

### 4.4 Full Path Disclosure via PHP Error (HIGH)

**Deskripsi:**
File `proses_hapus_user.php` dan `proses_delete_jadwal.php` memiliki `require_once "../connection.php"` yang **salah path** — seharusnya `../database/connection.php`. Karena `display_errors = On` di server, error yang muncul membocorkan **full path server**, **OS**, **web stack**, dan **PHP include_path**.

**URL:** `http://localhost/.../backend/process/proses_hapus_user.php?id=23`

**Proof of Concept:**

```
Warning: require_once(../connection.php): Failed to open stream: 
No such file or directory in 
D:\LauwbaAcademy\UK-PKL_Banjar\UK1\UK1-Rinjani\backend\process\proses_hapus_user.php 
on line 2

Fatal error: Uncaught Error: Failed opening required '../connection.php' 
(include_path='.;C:/laragon/etc/php/pear') in 
D:\LauwbaAcademy\UK-PKL_Banjar\UK1\UK1-Rinjani\backend\process\proses_hapus_user.php:2 
Stack trace: #0 {main} thrown in 
D:\LauwbaAcademy\UK-PKL_Banjar\UK1\UK1-Rinjani\backend\process\proses_hapus_user.php 
on line 2
```

**Informasi yang Bocor:**

| Info                 | Value                                          |
| -------------------- | ---------------------------------------------- |
| Full path server     | `D:\LauwbaAcademy\UK-PKL_Banjar\UK1\UK1-Rinjani\` |
| OS                   | Windows                                        |
| Web stack            | Laragon                                        |
| PHP include_path     | `.;C:/laragon/etc/php/pear`                    |

**Dampak:**

- Attacker tahu struktur direktori server
- Berguna untuk LFI / RFI / Path Traversal selanjutnya
- Bisa dipakai untuk fingerprinting web stack

**Severity:** 🟠 **HIGH** (CVSS 7.5)

**Remediasi:**

```php
// php.ini (production)
display_errors = Off
log_errors = On
error_log = /path/to/error.log
```

Perbaiki juga path `require_once`:

```php
require_once __DIR__ . '/../database/connection.php';
```

### 4.5 Business Logic — Validasi No. WhatsApp Lemah (MEDIUM)

**Deskripsi:**
Field `no_hp` tidak divalidasi format — bisa diinput string apapun tanpa validasi nomor telepon.

**Proof of Concept:**
Input pada form registrasi jamaah:

```
No HP: asdfghjkl      ← bukan angka
No HP: O8             ← hanya 2 karakter
No HP: 08789          ← hanya 5 digit (minimal 10-15 digit)
```

→ Semua **tersimpan di database**.

**Screenshot `tabel_jamaah.php`:**

```
No  NIK           Nama   No HP / WhatsApp
1   010101001     tt     08789
2   123456789...  <script>alert(1)</script>   O8
3   123456789012  test   asdfghjkl
```

**Dampak:**

- Data integrity rusak
- Spam registrasi dengan nomor palsu
- Bypass verifikasi OTP (jika ada)
- Notifikasi bisa dikirim ke nomor salah

**Severity:** 🟡 **MEDIUM** (CVSS 5.3)

**Remediasi:**

```php
if (!preg_match('/^[0-9]{10,15}$/', $no_hp)) {
    $error = "Nomor HP harus 10-15 digit angka.";
}
```

### 4.6 Information Disclosure via SQL Error (MEDIUM-HIGH)

**Deskripsi:**
File `tambah_pembayaran.php` mengeksekusi `INSERT INTO pembayaran (..., bukti_transfer, ...)`, tapi **kolom `bukti_transfer` tidak ada** di tabel `pembayaran` aktual. Error yang muncul ditampilkan langsung ke user tanpa sanitasi, membocorkan **nama tabel** dan **nama kolom**.

**URL:** `http://localhost/.../backend/tambah_pembayaran.php`

**Proof of Concept:**

```
Gagal menyimpan data transaksi: SQLSTATE[42S22]: Column not found: 
1054 Unknown column 'bukti_transfer' in 'field list'
```

**Informasi yang Bocor:**

| Info                 | Value                        |
| -------------------- | ---------------------------- |
| Nama tabel           | `pembayaran`                 |
| Nama kolom           | `bukti_transfer`             |
| Mode error           | Verbose (langsung ke user)   |
| Tipe exception       | `PDOException`               |

**Analisis Inkonsistensi:**

| Kode PHP                     | Database Aktual  |
| ---------------------------- | ---------------- |
| Ada `bukti_transfer`         | ❌ Tidak ada     |
| Ada `keberangkatan_id`       | ✅ Ada (tidak dipakai) |
| Ada `sisa_pembayaran`        | ✅ Ada (tidak dipakai) |

**Dampak:**

- Attacker tahu struktur database
- Membantu SQLi selanjutnya (nama tabel & kolom)
- Menandakan inkonsistensi antara source code & DB aktual
- Blocking fitur upload bukti transfer (tidak bisa dipakai)

**Severity:** 🟠 **MEDIUM-HIGH** (CVSS 6.5)

**Remediasi:**

```php
try {
    // ... INSERT query
} catch (PDOException $e) {
    error_log($e->getMessage());  // Log ke file
    $errorMessage = "Terjadi kesalahan saat menyimpan data.";  // Pesan generik
}
```

Sinkronkan juga struktur DB dengan source code:
```sql
ALTER TABLE `pembayaran` 
ADD COLUMN `bukti_transfer` VARCHAR(255) DEFAULT NULL;
```

## 5. Vektor yang Diuji & Aman

| Vektor                          | Status       | Bukti                                                                                                         |
| ------------------------------- | ------------ | ------------------------------------------------------------------------------------------------------------- |
| **SQL Injection**               | ✅ Aman      | Semua query pakai `prepare()` + `bindParam()`                                                                 |
| **XSS (Stored)**                | ✅ Aman      | Output di-escape `htmlspecialchars()` — input `<script>alert(1)</script>` muncul sebagai teks literal         |
| **Command Injection**           | ✅ Aman      | Tidak ada `exec()`, `system()`, `shell_exec()`                                                                |
| **File Upload → RCE**           | ⚠️ Terblokir | Validasi ekstensi whitelist, tapi tidak teruji karena bug DB `bukti_transfer`                                 |
| **Privilege Escalation (RBAC)** | ✅ Aman      | Jamaah (`tt`) yang akses `form_update_pendaftaran.php` langsung redirect ke login dengan alert "Akses Ditolak" |

## 6. Matriks Risiko

| #   | Temuan                                | Severity       | CVSS | Status     |
| --- | ------------------------------------- | -------------- | ---- | ---------- |
| 4.1 | Git Repository Exposure               | 🔴 Critical    | 9.1  | Confirmed  |
| 4.2 | Credential Leak di Git History        | 🔴 Critical    | 9.8  | Confirmed (login berhasil) |
| 4.3 | Directory Listing Aktif               | 🟡 Medium      | 5.3  | Confirmed  |
| 4.4 | Full Path Disclosure via PHP Error    | 🟠 High        | 7.5  | Confirmed  |
| 4.5 | Business Logic — Validasi NIK         | 🟡 Medium      | 5.3  | Confirmed  |
| 4.6 | Business Logic — Validasi No. WhatsApp | 🟡 Medium     | 5.3  | Confirmed  |
| 4.7 | Information Disclosure via SQL Error  | 🟠 Medium-High | 6.5  | Confirmed  |

**Total:** **2 Critical, 1 High, 1 Medium-High, 3 Medium**

## 7. Rekomendasi Perbaikan

### Prioritas 1 (Immediate)

1. **Hapus folder `.git`** dari server production — ini adalah penyebab utama kebocoran.
2. **Hash semua password** dengan bcrypt — khususnya admin `Rinjani` yang masih plaintext.
3. **Rotasi seluruh password** (admin, petugas, jamaah, DB) dengan password acak kuat.
4. **Hapus git history** yang mengandung credential & database dump:
   ```bash
   git filter-repo --path travel_haji_umroh.sql --invert-paths
   ```
   > **Catatan:** Menghapus file dari branch aktif **TIDAK CUKUP**. File masih bisa diakses via commit history.
5. **Set `display_errors = Off`** di `php.ini` production.
6. **Sinkronkan struktur DB dengan source code** — tambahkan kolom `bukti_transfer` atau ubah query.

### Prioritas 2 (Short-term)

7. **Nonaktifkan directory listing** (`Options -Indexes`) di semua folder.
8. **Perbaiki path `require_once`** di `proses_hapus_user.php` dan `proses_delete_jadwal.php`:
   ```php
   require_once __DIR__ . '/../database/connection.php';
   ```
9. **Validasi input NIK & No. WhatsApp** server-side.
10. **Tangkap exception** dengan pesan generik:
    ```php
    catch (PDOException $e) {
        error_log($e->getMessage());
        $errorMessage = "Terjadi kesalahan.";
    }
    ```

### Prioritas 3 (Long-term)

11. **Aktifkan HTTPS** (TLS/SSL).
12. **Set cookie flag**: `Secure`, `HttpOnly`, `SameSite=Strict`.
13. **Regenerasi session ID** setelah login (`session_regenerate_id(true)`).
14. **Implementasi CSRF token** di semua form POST.
15. **Rate limiting** pada form login & registrasi.
16. **Gunakan environment variable** untuk credential (`.env`).
17. **Aktifkan logging & monitoring** untuk akses `.git`, login gagal, dan upload mencurigakan.
18. **Implementasi 2FA** untuk akun admin.
19. **Security awareness training** untuk developer.

## 8. Panduan Aman Push ke GitHub

> **Prinsip:** Jangan pernah commit apapun yang kamu nggak mau dilihat publik.

### 8.1 Buat `.gitignore` yang Proper

```gitignore
# ===== CREDENTIAL & SECRET =====
.env
.env.*
!.env.example
*.key
*.pem
secrets.json

# ===== DATABASE =====
*.sql
*.sqlite
*.dump
db_*.sql
travel_haji_umroh.sql

# ===== CONFIG =====
config/database.php
backend/database/connection.php

# ===== BACKDOOR / FIX SCRIPTS =====
fix.php
reset_*.php
test_*.php
debug_*.php

# ===== BACKUP & LOG =====
*.bak
*.log
logs/

# ===== IDE & OS =====
.vscode/
.idea/
.DS_Store
Thumbs.db
```

### 8.2 Gunakan Environment Variable

Jangan hardcode credential di source code. Pakai `.env`:

```bash
composer require vlucas/phpdotenv
```

**`.env` (JANGAN di-commit):**
```env
DB_HOST=localhost
DB_NAME=travel_haji_umroh
DB_USER=root
DB_PASS=your_secure_password
```

**`config/database.php`:**
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    
    public function __construct() {
        $this->host     = $_ENV['DB_HOST'];
        $this->db_name  = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }
    // ...
}
```

### 8.3 Scan Credential Sebelum Push

```bash
# TruffleHog
pip install trufflehog
trufflehog git file://. --only-verified

# Gitleaks
docker run -v $(pwd):/path zricethezav/gitleaks:latest detect \
  --source="/path" --verbose

# Manual grep
grep -rniE "password|secret|api_key" . --exclude-dir=.git
```

### 8.4 Pre-commit Hook

Buat `.git/hooks/pre-commit`:

```bash
#!/bin/bash
PATTERNS=(
    "password\s*=\s*['\"][^'\"]+['\"]"
    "secret\s*=\s*['\"][^'\"]+['\"]"
    "BEGIN RSA PRIVATE KEY"
)

FILES=$(git diff --cached --name-only --diff-filter=ACM)

for file in $FILES; do
    for pattern in "${PATTERNS[@]}"; do
        if grep -qiE "$pattern" "$file" 2>/dev/null; then
            echo "❌ COMMIT DITOLAK: Potensi credential di file '$file'"
            exit 1
        fi
    done
done

echo "✅ Pre-commit check passed"
exit 0
```

### 8.5 Kalau Sudah Terlanjur Commit — Bersihkan History

```bash
# Pakai git-filter-repo
pip install git-filter-repo

git filter-repo --path travel_haji_umroh.sql --invert-paths

# Force push
git push origin --force --all
git push origin --force --tags

# Verifikasi
git log --all --full-history -- "travel_haji_umroh.sql"
# Output harus kosong
```

### 8.6 Checklist Sebelum Push

```
[ ] .gitignore sudah dibuat & proper
[ ] File .env TIDAK di-commit
[ ] File *.sql TIDAK di-commit
[ ] File config/database.php TIDAK di-commit
[ ] Credential di source code pakai environment variable
[ ] Pre-commit hook sudah dipasang
[ ] Scan credential pakai TruffleHog / Gitleaks
[ ] Repo GitHub di-set private
[ ] 2FA aktif di akun GitHub
```

## 9. Lampiran

### A. Struktur Folder Hasil Git Dump

```
hasil-git/
├── backend/
│   ├── classes/
│   │   ├── Auth.php
│   │   ├── Paket.php
│   │   ├── Pendaftaran.php
│   │   └── User.php
│   ├── components/
│   │   ├── bottom.php, footer.php, header.php
│   │   ├── sidebar.php, topbar.php
│   ├── database/connection.php
│   ├── process/
│   │   ├── proses_delete_jadwal.php
│   │   └── proses_hapus_user.php
│   ├── [40+ file .php]
├── frontend/
│   ├── components/, database/, pages/, partials/, template/
├── index.php
├── NOTE.md
└── TRAVEL-UMROH2
```

### B. Command yang Digunakan

```bash
# Reconnaissance
feroxbuster -u http://192.168.100.247/UK-PKL_Banjar/UK1/UK1-Rinjani \
  -w /usr/share/wordlists/dirb/common.txt

# Git dump
git-dumper http://192.168.100.247/UK-PKL_Banjar/UK1/UK1-Rinjani/.git/ ./hasil-git

# Credential search di git history
cd hasil-git
git log -p --all | grep -iE "password|secret"

# Cari file yang dihapus tapi masih di history
git log --all --diff-filter=D --name-only

# Tampilkan isi file yang dihapus
git show 109abf39^:travel_haji_umroh.sql

# Login test
curl -c cookies.txt -X POST \
  -d "username=Rinjani&password=rinjanicantik" \
  "http://localhost/.../backend/login.php"
```

### C. Timeline

| Tanggal         | Aktivitas                                                       |
| --------------- | --------------------------------------------------------------- |
| 28 Sep 2026     | Reconnaissance + git-dumper                                     |
| 28 Sep 2026     | Analisis source code + credential leak                          |
| 01 Okt 2026     | Login admin berhasil (`Rinjani:rinjanicantik`)                  |
| 01 Okt 2026     | Testing validasi input NIK & No. WhatsApp                       |
| 01 Okt 2026     | Testing upload bukti transfer → error `bukti_transfer`          |
| 01 Okt 2026     | Konfirmasi directory listing di `backend/process/`              |
| 01 Okt 2026     | Konfirmasi full path disclosure di `proses_hapus_user.php`      |

### D. Referensi

- OWASP Top 10 2021: A01 (Broken Access Control), A02 (Cryptographic Failures), A05 (Security Misconfiguration), A03 (Injection)
- CWE-538: File and Directory Information Exposure
- CWE-548: Exposure of Information Through Directory Listing
- CWE-798: Use of Hard-coded Credentials
- CWE-209: Generation of Error Message Containing Sensitive Information
- CWE-20: Improper Input Validation
- Git Docs: `git filter-repo` — https://github.com/newren/git-filter-repo

---

**— END OF REPORT —**

_Laporan ini dibuat untuk keperluan pembelajaran / authorized pentest. Penggunaan tanpa izin adalah ilegal._