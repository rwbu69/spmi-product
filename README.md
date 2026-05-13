# Sistem Informasi Penjaminan Mutu Internal (eSPMI)

Dokumentasi ini berisi panduan teknis langkah demi langkah untuk melakukan instalasi dan menjalankan proyek eSPMI secara lokal di lingkungan pengembangan berbasis Windows.

## Prasyarat Sistem & Instalasi via Chocolatey

Untuk memudahkan instalasi dependensi di sistem operasi Windows, kami merekomendasikan penggunaan **Chocolatey** (Package Manager untuk Windows).

### 1. Instalasi Chocolatey
Buka PowerShell sebagai Administrator (Run as Administrator) dan jalankan perintah berikut:
```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
```

### 2. Instalasi Dependensi (PHP, Composer, Node.js, MariaDB)
Setelah Chocolatey terinstal, Anda dapat menginstal semua perangkat lunak yang dibutuhkan eSPMI dengan satu baris perintah. Masih di PowerShell Administrator, ketik:
```powershell
choco install php composer nodejs mariadb -y
```
*(Catatan: Setelah proses instalasi selesai, pastikan Anda merestart terminal/PowerShell agar seluruh perintah baru dikenali oleh sistem).*

## Langkah Instalasi Proyek

1. **Kloning Repositori**
Kloning repositori ini ke dalam direktori lokal Anda dan masuk ke dalam folder proyek.
```bash
git clone <url-repositori-anda>
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
Buka file `.env` di code editor Anda. Cari blok yang mengatur database dan ubah `DB_CONNECTION` menjadi `mariadb` (karena kita menggunakan MariaDB dari instalasi choco). Sesuaikan nama database, username, dan password-nya.

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spmi_project
DB_USERNAME=root
DB_PASSWORD=
```
*(Pastikan Anda telah membuat database kosong bernama `spmi_project` di MariaDB melalui klien manajemen database atau command line).*

## Migrasi dan Seeding Database

Jalankan perintah berikut untuk membangun struktur tabel di database dan mengisi data awal (*seeder*) yang diperlukan untuk menjalankan dan masuk ke aplikasi.

```bash
php artisan migrate:fresh --seed
```

Data kredensial bawaan yang dihasilkan oleh *seeder* untuk keperluan pengujian lokal adalah:
- **Username:** `superadmin`
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
