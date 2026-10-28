# Panduan Deploy INLISLite 3.3 — inlislite.solokkab.go.id

Panduan ini untuk unggah manual (cPanel/File Manager/FTP). Semua perintah
diasumsikan dijalankan dari folder aplikasi di server.

---

## 1. Yang perlu disiapkan

| Kebutuhan | Keterangan |
|---|---|
| PHP | Minimal 8.2. Disarankan 8.3. |
| Ekstensi PHP | `intl`, `mbstring`, `mysqli`, `curl`, `openssl`, `json`, `xml`, `zip`, `gd` |
| Database | MySQL 8.x atau MariaDB 10.4+ |
| SSL | Aktif untuk `inlislite.solokkab.go.id` |
| File aplikasi | Seluruh folder proyek, kecuali isi cache/session/log |
| Database | `deploy/inlislite_v33_migrasi.sql.gz` (sudah disiapkan) |

> Ekstensi `zip` penting untuk ekspor Excel (PhpSpreadsheet). Jika hosting
> belum mengaktifkannya, minta hosting mengaktifkan `zip`.

---

## 2. Susunan folder di server (disarankan)

Document root subdomain **harus diarahkan ke folder `public`**, bukan ke
folder utama aplikasi.

```text
/home/USER/inlislite/
├── app/
├── public/          <-- Document Root subdomain
│   ├── index.php
│   ├── .htaccess
│   ├── assets/
│   └── uploads/
├── vendor/
├── writable/
├── .env             <-- dibuat di server, jangan di dalam public
└── ...
```

Di cPanel: **Domains / Subdomains → inlislite.solokkab.go.id → Document Root**
lalu arahkan ke `/home/USER/inlislite/public`.

Jika panel tidak mengizinkan mengubah document root, buat subdomain baru
dengan document root yang menunjuk ke folder `public` tersebut.

---

## 3. Impor database

1. Buat database dan user database di cPanel, misalnya:
   - Database: `USER_inlislite`
   - User: `USER_inlislite`
   - Beri hak akses penuh user ke database.
2. Impor file `deploy/inlislite_v33_migrasi.sql.gz`.
   - **cPanel → phpMyAdmin → Import**.
   - Jika phpMyAdmin menolak `.gz`, ekstrak dulu menjadi
     `inlislite_v33_migrasi.sql` lalu impor.
   - Jika ukuran melebihi batas upload phpMyAdmin, gunakan SSH:

```bash
gunzip < inlislite_v33_migrasi.sql.gz | mysql -u USER_DB -p NAMA_DB
```

3. Setelah impor, pastikan tabel `catalogs` memiliki kolom `ISPopuler`.
   Kolom ini sudah termasuk dalam dump. Jika database dibuat ulang dari
   skema lain, tambahkan:

```sql
ALTER TABLE catalogs ADD COLUMN ISPopuler TINYINT NOT NULL DEFAULT 0 AFTER IsOPAC;
```

---

## 4. Unggah file aplikasi

Unggah seluruh folder proyek ke `/home/USER/inlislite/`, lalu:

1. **Jangan unggah** isi folder berikut (boleh dibuat ulang):
   - `writable/cache/*`
   - `writable/session/*`
   - `writable/logs/*`
   - `writable/debugbar/*`
2. Pastikan folder berikut ada dan dapat ditulis:
   - `writable/` dan seluruh subfoldernya
   - `public/uploads/` dan subfoldernya
3. Permission yang umum dipakai di cPanel:
   - Folder: `755` (atau `775` bila perlu)
   - File: `644`
   - `writable/` dan `public/uploads/`: `775`

File pengembangan di root proyek (`check_height.php`, `fix_a45.php`,
`fix_a45_2.php`, `test_payload.php`, `test_phpword.php`,
`update_tcpdf_height.php`, `update_tcpdf_height2.php`,
`update_templates.php`) tidak perlu diunggah. File-file itu berada di luar
`public`, tetapi lebih aman tidak disertakan.

---

## 5. Buat file `.env` produksi

1. Salin `deploy/.env.production.example` menjadi `.env` di folder utama
   aplikasi (sejajar `app/`, `public/`, `vendor/`).
2. Isi bagian database:

```env
database.default.hostname = localhost
database.default.database = USER_inlislite
database.default.username = USER_inlislite
database.default.password = PASSWORD_DB

database.data.hostname = localhost
database.data.database = USER_inlislite
database.data.username = USER_inlislite
database.data.password = PASSWORD_DB

database.backend.hostname = localhost
database.backend.database = USER_inlislite
database.backend.username = USER_inlislite
database.backend.password = PASSWORD_DB
```

3. Pastikan URL utama:

```env
CI_ENVIRONMENT = production
app.baseURL = 'https://inlislite.solokkab.go.id/'
app.siteURL = 'https://inlislite.solokkab.go.id'
app.uriProtocol = 'REQUEST_URI'
app.checkURL = false
```

4. Ganti `security.TOKEN_SECRET` dengan string acak panjang. Ini akan
   membuat semua sesi login lama tidak berlaku; pengguna cukup login ulang.

5. hCaptcha:
   - Sementara ini template memakai `HCAPTCHA_DISABLED = true` karena key
     lama belum tentu valid untuk domain baru.
   - Setelah memiliki key resmi untuk `inlislite.solokkab.go.id`, isi
     `HCAPTCHA_SITE_KEY` dan `HCAPTCHA_SECRET_KEY`, lalu ubah menjadi
     `HCAPTCHA_DISABLED = false`.

---

## 6. Bersihkan cache setelah upload

Hapus isi:

```text
writable/cache/
writable/session/
writable/logs/
```

Ini penting karena cache route lokal berisi path Windows
(`D:\inlislite_v33\...`) yang tidak valid di server.

Jika memakai SSH:

```bash
rm -rf writable/cache/* writable/session/* writable/logs/*
```

---

## 7. Aktifkan HTTPS

Pastikan SSL subdomain sudah aktif. Jika ingin memaksa HTTPS, tambahkan di
awal blok `mod_rewrite` pada `public/.htaccess`:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

Jika server berada di balik proxy/Cloudflare, gunakan header
`X-Forwarded-Proto` dan jangan aktifkan aturan di atas tanpa pengujian.

---

## 8. Uji setelah deploy

Buka dan pastikan semuanya berhasil:

| URL | Harapan |
|---|---|
| `https://inlislite.solokkab.go.id/` | Beranda tampil |
| `/opac` | OPAC tampil, pencarian berjalan |
| `/opac/browse` | Halaman browse tampil |
| `/opac/statistics` | Statistik katalog tampil |
| `/opac/pangkalan-data` | Halaman OneSearch/repositori tampil |
| `/opac/detail/12451` | Detail katalog tampil |
| `/login` | Halaman login staf tampil |
| `/dashboard` | Setelah login admin, dashboard tampil |
| `/parameter` | Halaman parameter tampil |
| `/anggota` | Daftar anggota dan DataTable tampil |
| `/katalog` | Daftar katalog dan DataTable tampil |
| `/sirkulasi-peminjaman/index` | Daftar peminjaman tampil |
| `/sirkulasi-pengembalian/index` | Daftar pengembalian tampil |

Lalu cek:

- Pencarian OPAC kata kunci `Botani`.
- Detail katalog `12451` menampilkan eksemplar dan status.
- Login admin memakai username `admin` dan password baru.
- Ganti password admin bawaan segera setelah login pertama.

---

## 9. Setelah go-live

1. **Ganti password admin** (`admin` / `Solok#2026!` hanya untuk lokal).
2. Periksa **Parameter** dan lengkapi:
   - NPP perpustakaan
   - Email resmi
   - Logo perpustakaan
   - Jam operasional
3. Periksa menu **Pangkalan Data** di navbar/footer.
4. Untuk mengubah daftar tautan OneSearch/repositori, edit
   `app/Config/OpacLinks.php` lalu unggah ulang file tersebut.
5. Lakukan backup database dan folder `public/uploads` secara berkala.

---

## 10. Catatan data lama

- Data anggota lama berjumlah 1.311, tetapi akun login OPAC hanya tersedia
  untuk akun yang ada di tabel `users`. Ini sesuai keputusan untuk
  mempertahankan kondisi saat ini.
- Catatan sirkulasi lama yang anggota/header-nya tidak ditemukan tetap
  disimpan apa adanya dan tidak ditampilkan di daftar admin karena query
  memakai INNER JOIN.
- Tautan OneSearch dan repositori bersifat tautan eksternal, bukan
  integrasi API/SRU.
