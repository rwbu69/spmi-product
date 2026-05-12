# Sistem Informasi Penjaminan Mutu Internal (eSPMI)

Dokumentasi ini berisi panduan teknis langkah demi langkah untuk melakukan instalasi dan menjalankan proyek eSPMI secara lokal di lingkungan pengembangan.

## Prasyarat Sistem

Sebelum memulai instalasi, pastikan sistem Anda telah memenuhi persyaratan berikut:
- PHP 8.2 atau lebih baru
- Composer 2.0 atau lebih baru
- Node.js versi 18.x atau lebih baru beserta NPM
- Database Server (MySQL, PostgreSQL, atau SQLite)

## Langkah Instalasi

1. Kloning Repositori
Kloning repositori ini ke dalam direktori lokal Anda dan masuk ke dalam folder proyek.

```bash
git clone <url-repositori-anda>
cd spmi-product
```

2. Instalasi Dependensi Backend
Jalankan Composer untuk mengunduh seluruh dependensi PHP yang dibutuhkan oleh Laravel.

```bash
composer install
```

3. Instalasi Dependensi Frontend
Jalankan NPM untuk mengunduh seluruh dependensi Node.js yang dibutuhkan oleh Vue dan Inertia.

```bash
npm install
```

## Konfigurasi Environment

1. Buat salinan file konfigurasi environment dari template yang disediakan.

```bash
cp .env.example .env
```

2. Buat application key baru.

```bash
php artisan key:generate
```

3. Buka file `.env` dan konfigurasikan koneksi database Anda sesuai dengan lingkungan lokal yang digunakan.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=root
DB_PASSWORD=
```

## Migrasi dan Seeding Database

Jalankan perintah berikut untuk membangun struktur tabel di database dan mengisi data awal (seeder) yang diperlukan untuk menjalankan aplikasi.

```bash
php artisan migrate:fresh --seed
```

Data kredensial bawaan yang dihasilkan oleh seeder untuk keperluan pengujian lokal adalah:
- Username: superadmin
- Password: password

## Menjalankan Aplikasi

Aplikasi ini menggunakan arsitektur Laravel dan Vue (Inertia), sehingga Anda perlu menjalankan dua server lokal secara bersamaan pada terminal yang berbeda.

1. Jalankan command backend dan frontend

```bash
composer run dev
```

Aplikasi kini dapat diakses melalui browser pada alamat [http://localhost:8000](http://localhost:8000) atau [http://127.0.0.1:8000](http://127.0.0.1:8000).
