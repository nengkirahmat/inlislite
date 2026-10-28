# INLISLite v3.3 — Perpustakaan Umum Kabupaten Solok

Repositori ini adalah **turunan (fork) dari [INLISLite-Perpusnas/INLISLIteV33](https://github.com/INLISLite-Perpusnas/INLISLIteV33)** yang disesuaikan untuk Perpustakaan Umum Kabupaten Solok, ditambah beberapa fitur dan perbaikan. Daftar lengkap perbedaannya ada di bagian [Perubahan dibanding repo asli](#perubahan-dibanding-repo-asli).

## Apa itu INLISLite?

**INLISLite** adalah aplikasi otomasi perpustakaan untuk pencarian koleksi, peminjaman, keanggotaan, hingga akses literatur digital, yang dirancang untuk membantu seluruh perpustakaan di Indonesia. Aplikasi ini dikembangkan oleh **Divisi Pusat Data dan Informasi, Perpustakaan Nasional Republik Indonesia**, dan dibangun di atas framework **CodeIgniter 4**.

---

## Perubahan dibanding repo asli

- **Basis kode:** commit `eb6dba9` ("menambahan filter kelas dan fakultas") dari branch `main` repo asli.
- **Riwayat commit:** repositori ini dimulai dari satu commit tanpa riwayat lama, karena riwayat repo asli memuat *secret* pada file konfigurasi contoh (`env.sample`, `.env copy 4`) sehingga ditolak oleh GitHub push protection.

### 1. Fitur baru: Statistik

Tiga halaman statistik publik ada di menu **Home → Statistik**, dan bisa dibuka tanpa login. Satu laporan statistik tersedia untuk admin.

| Halaman | URL | Akses | Isi |
|---|---|---|---|
| Statistik Kunjungan | `/opac/statistics_kunjungan` | Publik | Total kunjungan (anggota, non-anggota, rombongan), tren per bulan, komposisi pengunjung, jenis kelamin, hari kunjungan, pekerjaan, pendidikan, ruang yang dikunjungi, kategori dan instansi rombongan, rekap per tahun |
| Statistik Peminjaman | `/opac/statistics_peminjaman` | Publik | Buku dipinjam, jumlah peminjam, buku sedang dipinjam dan yang lewat jatuh tempo, tren per bulan dan per hari, 10 buku terpopuler, bidang ilmu (DDC), jenis anggota, kategori koleksi, rekap per tahun |
| Statistik Pengembalian | `/opac/statistics_pengembalian` | Publik | Buku dikembalikan, persentase tepat waktu, keterlambatan, median lama pinjam, sebaran lama pinjam dan keterlambatan, pinjaman yang belum kembali, rekap per tahun |
| Statistik Kunjungan (admin) | `/report/statistik-kunjungan` | Login, menu **Laporan** | KPI, grafik bulanan dan harian, komposisi, rekap bulanan dan tahunan, versi cetak |

Catatan perhitungan:
- **Kunjungan rombongan** dihitung per orang (jumlah personel), bukan per rombongan.
- **Peminjaman dan pengembalian** dihitung per eksemplar buku.
- **Pengembalian terlambat** berarti tanggal kembali melewati tanggal jatuh tempo.
- **Halaman publik** hanya menampilkan angka agregat dan judul buku, tanpa nama anggota.
- **Grafik** memakai Chart.js 3.9.1 dari CDN jsDelivr, jadi browser pengunjung perlu akses internet.

### 2. Penyesuaian deployment Solok (OPAC, OneSearch, OAI-PMH)

- **Halaman Pangkalan Data & Repositori** (`/opac/pangkalan-data`): daftar tautannya diatur di `app/Config/OpacLinks.php`. Menu **Pangkalan Data** dan tautan **OneSearch Indonesia** juga ditambahkan di navbar dan footer OPAC.
- **OAI-PMH** (`app/Modules/SubModule/Oai`) disesuaikan agar bisa dipanen OneSearch:
  - `resumptionToken` membawa `metadataPrefix`, `from`, `until`, dan `set`.
  - Elemen `<request>` berisi base URL repository.
  - `dc:type` mengikuti nama worksheet, seperti INLISLite lama.
  - `dc:source` menaut ke halaman detail OPAC.
- **Login hCaptcha** otomatis nonaktif di environment `development`. Di production tetap aktif, kecuali di-set `HCAPTCHA_DISABLED=true` atau `HCAPTCHA_SITE_KEY` kosong.
- **Detail katalog OPAC**: gambar sampul hanya dipakai jika filenya ada. Jika tidak ada, tampil sampul default.
- **Detail anggota (admin)**: foto, nama, nomor anggota, dan email tampil dengan benar, dan tombol **Cetak Kartu** mengarah ke `anggota/printanggota`.
- **Konfigurasi server**: `.htaccess`, `public/.htaccess`, `public/.user.ini`, contoh environment `deploy/.env.production.example`, dan panduan unggah manual `deploy/README-DEPLOY.md`.

### 3. Perbaikan lain

- **Navbar Home (OPAC):**
  - Menu tidak lagi menutupi logo dan nama perpustakaan.
  - Lebar navbar maksimal 1440px.
  - Di layar di bawah 1200px, menu masuk ke tombol hamburger.
- **Buku Tamu Rombongan:** pesan error ditampilkan bila penyimpanan gagal.

### 4. File yang ditambah atau diubah

<details>
<summary>Daftar file</summary>

**Statistik dan perbaikan lain**
- `app/Modules/Laporan/StatistikKunjungan/` — modul baru (Config, Controllers, Models, Views)
- `app/Modules/Opac/Models/StatistikSirkulasiModel.php` — baru
- `app/Modules/Opac/Views/statistics_kunjungan.php`, `statistics_peminjaman.php`, `statistics_pengembalian.php` — baru
- `app/Modules/Opac/Views/partials/statistics_style.php`, `statistics_helpers.php` — baru
- `app/Modules/Opac/Controllers/Opac.php` — method `statistics_kunjungan`, `statistics_peminjaman`, `statistics_pengembalian`
- `app/Modules/Opac/Config/Routes.php` — route halaman statistik
- `app/Config/Filters.php` — halaman statistik publik dikecualikan dari filter `session` dan `permissions`
- `app/Views/layout/opac/layout.php` — menu Statistik dan perbaikan navbar
- `app/Modules/GuestBook/Views/add_rombongan.php` — pesan error

**Deployment Solok**
- `app/Config/OpacLinks.php`, `app/Modules/Opac/Views/pangkalan_data.php` — baru
- `app/Modules/Core/Auth/Controllers/Auth.php`, `app/Modules/Core/Auth/Views/authlogin.php`
- `app/Modules/Opac/Controllers/Opac.php`, `app/Modules/Opac/Config/Routes.php`, `app/Modules/Opac/Views/detail.php`
- `app/Modules/SubModule/Oai/Config/Routes.php`, `app/Modules/SubModule/Oai/Controllers/Oai.php`
- `app/Modules/SubModule/Keanggotaan/Anggota/Views/detail.php`
- `app/Config/Filters.php`, `app/Views/layout/opac/layout.php`
- `.htaccess`, `public/.htaccess`, `public/.user.ini`, `.gitignore`
- `deploy/.env.production.example`, `deploy/README-DEPLOY.md` — baru

</details>

### 5. Yang belum termasuk dari repo asli

Per 28 Oktober 2026, repo asli sudah memiliki **59 commit lebih baru** daripada basis kode di atas. Contohnya: modul update source code, perpustakaan keliling, perbaikan survei, backup database, serta perbaikan eksemplar dan foto anggota. Commit-commit itu **belum** digabung ke repositori ini.

Riwayat repositori ini tidak terhubung dengan repo asli, jadi pembaruan dari repo asli perlu diambil per commit (`git cherry-pick`) atau dibandingkan per file:

```bash
git remote add upstream https://github.com/INLISLite-Perpusnas/INLISLIteV33.git
git fetch upstream main
git log --oneline eb6dba9..upstream/main   # daftar commit yang belum masuk
git cherry-pick <commit>                   # ambil commit tertentu
```

---

## Persyaratan Server

| Kebutuhan | Keterangan |
|---|---|
| PHP | **8.2 atau lebih baru**, sesuai syarat CodeIgniter 4.7.4. Berjalan di PHP 8.3 (lokal) dan PHP 8.4 (server Solok). |
| Ekstensi PHP | `intl`, `mbstring`, `mysqli`, `curl`, `openssl`, `json`, `xml`, `zip`, `gd` |
| Database | MySQL 8.x atau MariaDB 10.4+ |
| Lainnya | Composer, dan web server Apache/Nginx (atau server bawaan PHP untuk development) |

---

## Cara Menjalankan Aplikasi

### 1. Clone project

```bash
git clone https://github.com/nengkirahmat/inlislite.git
cd inlislite
```

### 2. Install dependency

Folder `vendor/` tidak disertakan di repositori. Install dengan:

```bash
composer install
```

### 3. Buat file `.env`

Salin `env.sample` menjadi `.env`:

```bash
cp env.sample .env
```

Lalu sesuaikan minimal bagian berikut:

```env
CI_ENVIRONMENT = development        # gunakan production di server

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = inlislite_v33
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi

# Grup "data" dipakai banyak modul (termasuk statistik). Isi dengan database yang sama.
database.data.hostname = localhost
database.data.database = inlislite_v33
database.data.username = root
database.data.password =
database.data.DBDriver = MySQLi

# Opsional. Kosongkan untuk menonaktifkan hCaptcha di halaman login.
HCAPTCHA_SITE_KEY =
HCAPTCHA_SECRET_KEY =
```

Untuk server production, lihat contoh lengkap di `deploy/.env.production.example`.

### 4. Siapkan database

File database **tidak** disertakan di repositori. Gunakan salah satu cara berikut:

- **Instalasi baru:** buat database, lalu impor file `inlislite_v33.sql` dari paket distribusi INLISLite v3.3:

  ```sql
  CREATE DATABASE inlislite_v33 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
  ```

  ```bash
  mysql -u root -p inlislite_v33 < path/to/inlislite_v33.sql
  ```

- **Migrasi dari INLISLite 3.2:** impor database v3.3 kosong, lalu pindahkan data dari database lama dengan aplikasi migrator.

  Setelah migrasi, cek apakah tabel `groupguesses` sudah memiliki kolom `CountTk`. Jika belum, jalankan:

  ```bash
  mysql -u root -p inlislite_v33 < app/Database/add_countTk_groupguesses.sql
  ```

  Tanpa kolom ini, penyimpanan Buku Tamu Rombongan akan gagal.

### 5. Siapkan folder upload dan writable

Folder `public/uploads/` tidak disertakan di repositori, karena berisi file unggahan seperti foto anggota, sampul katalog, logo, dan banner.

- **Pindah dari instalasi lama:** salin folder `public/uploads/` dari instalasi tersebut.
- **Instalasi baru:** buat folder `public/uploads/`, lalu pastikan ada gambar default `public/uploads/default/nophoto.jpg`.

Pastikan folder `writable/` (cache, logs, session, uploads) dapat ditulis oleh web server.

### 6. Jalankan

Web server **harus diarahkan ke folder `public/`**, bukan ke root project.

**Development (server bawaan PHP):**

```bash
php spark serve
```

Aplikasi berjalan di `http://localhost:8080`.

**Laragon / Apache / Nginx:** arahkan document root virtual host ke folder `inlislite/public`. Contoh untuk Nginx:

```nginx
server {
    listen 80;
    server_name inlislite.test;
    root /path/to/inlislite/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Untuk hosting cPanel (unggah manual), ikuti panduan `deploy/README-DEPLOY.md`.

### 7. Verifikasi

Buka `app.baseURL` di browser, lalu cek:

- [ ] Halaman Home tampil, dan menu **Statistik** berisi Katalog, Anggota, Kunjungan, Peminjaman, dan Pengembalian.
- [ ] `/opac/statistics_kunjungan`, `/opac/statistics_peminjaman`, dan `/opac/statistics_pengembalian` terbuka tanpa login.
- [ ] Login sebagai admin, lalu menu **Laporan → Statistik Kunjungan** dapat dibuka.

---

## Memperbarui aplikasi yang sudah berjalan

1. Backup file aplikasi dan database.
2. Ambil kode terbaru (`git pull`), atau unggah file yang berubah.
3. Jalankan `composer install` bila `composer.lock` berubah.
4. **Hapus file `writable/cache/routes_list`.**

   Aplikasi menyimpan daftar modul (namespace) di file cache ini. Selama file itu belum dihapus, modul baru seperti `StatistikKunjungan` tidak terdaftar dan halamannya menjadi 404.
5. Logout, lalu login kembali agar menu baru muncul.

### Menu admin Statistik Kunjungan

Menu dan hak akses admin tersimpan di database (`c_menus`, `auth_permissions`, `auth_groups_permissions`). Pada database yang belum memilikinya, tambahkan dengan SQL berikut. SQL ini aman dijalankan berulang kali.

<details>
<summary>SQL menu & hak akses</summary>

```sql
INSERT INTO c_menus (name, parent, controller, slug, icon, permission, file_image, type, category_id, category, sort, description, active, created_at)
SELECT 'Statistik Kunjungan', p.id, 'report/statistik-kunjungan', 'report/statistik-kunjungan', 'fas fa-chart-line', 'access|index', '', 'menu', p.category_id, 'backend-menu', 39, '', 1, NOW()
FROM c_menus p
WHERE p.controller = 'menu-laporan'
  AND NOT EXISTS (SELECT 1 FROM c_menus WHERE controller = 'report/statistik-kunjungan')
LIMIT 1;

INSERT INTO auth_permissions (name, route, menu, description)
SELECT 'report/statistik-kunjungan/access', 'report/statistik-kunjungan', 'Statistik Kunjungan', 'Akses menu Statistik Kunjungan'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM auth_permissions WHERE name = 'report/statistik-kunjungan/access');

INSERT INTO auth_permissions (name, route, menu, description)
SELECT 'report/statistik-kunjungan/index', 'report/statistik-kunjungan', 'Statistik Kunjungan', 'Lihat halaman Statistik Kunjungan'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM auth_permissions WHERE name = 'report/statistik-kunjungan/index');

INSERT INTO auth_groups_permissions (group_id, permission_id, category, branch_id)
SELECT g.id, p.id, 0, 0
FROM auth_groups g
JOIN auth_permissions p ON p.name IN ('report/statistik-kunjungan/access', 'report/statistik-kunjungan/index')
WHERE g.name = 'admin'
  AND NOT EXISTS (
      SELECT 1 FROM auth_groups_permissions gp
      WHERE gp.group_id = g.id AND gp.permission_id = p.id
  );
```

</details>

---

## Troubleshooting

| Masalah | Kemungkinan penyebab dan solusi |
|---|---|
| Halaman kosong / error 500 | Cek permission folder `writable/` dan log di `writable/logs/`. |
| Error koneksi database | Cek kredensial `database.default.*` **dan** `database.data.*` di `.env`. |
| CSS/JS tidak muncul | `app.baseURL` di `.env` belum sesuai dengan URL akses. |
| `index.php` tidak ditemukan | Document root belum diarahkan ke folder `public/`. |
| Halaman modul baru 404 | Hapus `writable/cache/routes_list`. |
| Grafik statistik tidak tampil | Browser tidak bisa mengakses CDN `cdn.jsdelivr.net`. |
| Simpan Buku Tamu Rombongan gagal | Kolom `groupguesses.CountTk` belum ada. Jalankan `app/Database/add_countTk_groupguesses.sql`. |
| Login meminta hCaptcha di lokal | Set `CI_ENVIRONMENT = development`, atau `HCAPTCHA_DISABLED=true`. |

---

## Sumber & Lisensi

- Repo asli: https://github.com/INLISLite-Perpusnas/INLISLIteV33 — dikembangkan oleh Divisi Pusat Data dan Informasi, Perpustakaan Nasional Republik Indonesia
- Dokumentasi CodeIgniter 4: https://codeigniter4.github.io/userguide/
- Lisensi: lihat file [`LICENSE`](LICENSE)

#SalamLiterasi
