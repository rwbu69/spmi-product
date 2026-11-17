# Sistem Informasi Penjaminan Mutu Internal (eSPMI)

Dokumentasi ini berisi panduan teknis langkah demi langkah untuk melakukan instalasi dan menjalankan proyek eSPMI secara lokal di lingkungan pengembangan berbasis Windows.

## Prasyarat Sistem & Instalasi Dependensi

Untuk memudahkan instalasi dan pengelolaan versi dependensi di sistem operasi Windows (terutama mengunci versi **PHP 8.4**), kami merekomendasikan penggunaan **Scoop** (Package Manager untuk Windows).

### 1. Instalasi Scoop
Buka PowerShell (tanpa perlu Administrator) dan jalankan perintah berikut:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
Invoke-RestMethod -Uri [https://get.scoop.sh](https://get.scoop.sh) | Invoke-Expression
```

### 2. Instalasi Dependensi (PHP 8.4, Composer, Node.js, MariaDB)
Setelah Scoop terinstal, tambahkan *bucket* `versions` untuk mengakses paket PHP 8.4, lalu instal seluruh perangkat lunak yang dibutuhkan eSPMI:
```powershell
scoop bucket add versions
scoop install versions/php84 composer nodejs mariadb
scoop reset php84
```
*(Catatan: Jika Anda tetap menggunakan **Chocolatey** di PowerShell Administrator, gunakan `choco install php --version=8.4.27 -y` lalu instal `composer nodejs mariadb` agar versi PHP tidak otomatis terpasang ke 8.5+).*

### 3. Konfigurasi Ekstensi PHP (`php.ini`)
Agar Laravel dapat terhubung ke database MariaDB tanpa error `could not find driver`, aktifkan file `php.ini` dan ekstensi yang diperlukan dengan menjalankan perintah berikut di PowerShell:
```powershell
$ini = "$env:USERPROFILE\scoop\apps\php84\current\php.ini"
Copy-Item "$env:USERPROFILE\scoop\apps\php84\current\php.ini-development" $ini
(Get-Content $ini) -replace '^;\s*extension_dir\s*=\s*"ext"', 'extension_dir = "ext"' `
                   -replace '^;\s*extension=(curl|fileinfo|mbstring|openssl|pdo_mysql|mysqli|pdo_sqlite|zip)\b', 'extension=$1' |
  Set-Content $ini
```
*(Pastikan versi dan ekstensi sudah aktif dengan menjalankan `php -v` untuk melihat versi `PHP 8.4.x`, serta `php -m` untuk memastikan `pdo_mysql` terdaftar).*

## Langkah Instalasi Proyek

1. **Kloning Repositori**
Kloning repositori ini ke dalam direktori lokal Anda dan masuk ke dalam folder proyek.
```bash
git clone [https://github.com/rwbu69/spmi-product.git](https://github.com/rwbu69/spmi-product.git)
cd spmi-product
```

2. **Instalasi Dependensi Backend (PHP/Laravel)**
Jalankan Composer untuk mengunduh seluruh pustaka yang dibutuhkan oleh sistem.
```bash
composer install
```

3. **Instalasi Dependensi Frontend (Vue/Inertia)**
Jalankan NPM untuk mengunduh pustaka antarmuka.
```bash
npm install
```

## Konfigurasi Environment (`.env`)

File `.env` digunakan untuk mengatur variabel lingkungan yang krusial seperti kredensial keamanan aplikasi dan sambungan ke database.

1. **Buat file konfigurasi environment** dari template yang disediakan:
```bash
cp .env.example .env
```

2. **Generate Application Key (`APP_KEY`)**
Perintah ini akan secara otomatis mengisi baris `APP_KEY` di dalam file `.env`. Kunci ini sangat penting untuk mengenkripsi sesi pengguna dan menjaga integritas data secara aman.
```bash
php artisan key:generate
```

3. **Pengaturan Koneksi Database (MariaDB)**
Buka file `.env` di code editor Anda. Cari blok yang mengatur database dan ubah `DB_CONNECTION` menjadi `mariadb`. Sesuaikan nama database, username, dan password-nya.

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spmi_project
DB_USERNAME=root
DB_PASSWORD=
```
*(Pastikan layanan MariaDB sudah berjalan dan Anda telah membuat database kosong bernama `spmi_project` melalui klien manajemen database atau command line).*

## Migrasi dan Seeding Database

Jalankan perintah berikut untuk membangun struktur tabel di database dan mengisi data awal (*seeder*) yang diperlukan untuk menjalankan dan masuk ke aplikasi.

```bash
php artisan migrate:fresh --seed
```

Data kredensial bawaan yang dihasilkan oleh *seeder* untuk keperluan pengujian lokal adalah:
- **Username:** `admin`
- **Password:** `password`

## Menjalankan Aplikasi

Aplikasi ini menggunakan arsitektur Laravel dan Vue (Inertia), sehingga kita akan menjalankan server backend dan *build watcher* frontend secara bersamaan.

Cukup ketik perintah berikut di dalam terminal:

```bash
composer run dev
```

Aplikasi kini dapat diakses melalui browser pada alamat [http://localhost:8000](http://localhost:8000).

---

## Panduan Deployment Server

Bila Anda sudah siap mengunggah aplikasi ini ke lingkungan produksi, silakan merujuk pada dokumen berikut:
**[Tutorial Deployment & Hosting (deployment.md)](deployment.md)**
*(Berisi panduan lengkap untuk instalasi di VPS Ubuntu, Server Lokal Kampus, maupun Shared Hosting).*