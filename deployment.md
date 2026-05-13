# Panduan Deployment eSPMI

Dokumentasi ini berisi langkah-langkah untuk mendeploy aplikasi eSPMI (berbasis Laravel + Vue Inertia) ke berbagai jenis environment server di level produksi.

---

## 1. Deployment di VPS (Virtual Private Server) Ubuntu

Ini adalah metode yang paling disarankan untuk mendapatkan performa tinggi, keamanan server mandiri, dan kontrol maksimal atas aplikasi.

### Prasyarat
- VPS dengan sistem operasi Ubuntu 22.04 / 24.04
- Akses root / user sudo
- Domain yang sudah diarahkan ke alamat IP VPS (A Record)

### Langkah-langkah
1. **Update Sistem & Instalasi Nginx, PHP, MariaDB, Node.js**
   Buka terminal SSH Anda dan jalankan skrip berikut untuk memasang semua kebutuhan *stack* sistem:
   ```bash
   sudo apt update && sudo apt upgrade -y
   sudo apt install nginx mariadb-server curl git unzip -y
   
   # Setup repository PHP
   sudo add-apt-repository ppa:ondrej/php
   sudo apt update
   sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip -y
   
   # Setup Node.js v20
   curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
   sudo apt install -y nodejs
   ```

2. **Instalasi Composer (Package Manager PHP)**
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

3. **Setup Database MariaDB**
   Masuk ke konsol MySQL dan buat akses database untuk SPMI:
   ```bash
   sudo mysql -u root
   CREATE DATABASE spmi_db;
   CREATE USER 'spmi_user'@'localhost' IDENTIFIED BY 'password_kuat_anda';
   GRANT ALL PRIVILEGES ON spmi_db.* TO 'spmi_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

4. **Kloning & Konfigurasi Aplikasi**
   Letakkan aplikasi di direktori `/var/www`:
   ```bash
   cd /var/www
   sudo git clone <repo-url-anda> spmi
   sudo chown -R $USER:www-data /var/www/spmi
   cd spmi
   
   # Install dependensi (optimasi produksi)
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   
   # Setup Environment
   cp .env.example .env
   php artisan key:generate
   ```
   *Penting: Buka file `.env` dengan nano/vim dan ganti `APP_ENV=production`, `APP_DEBUG=false`, serta isikan `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` sesuai data pada langkah 3.*

   Jalankan migrasi struktur database: 
   ```bash
   php artisan migrate --force --seed
   ```

5. **Pengaturan Hak Akses Folder (Permissions)**
   Web server butuh akses penulisan untuk menyimpan sesi, log, dan unggahan file dokumen:
   ```bash
   sudo chown -R www-data:www-data /var/www/spmi/storage /var/www/spmi/bootstrap/cache
   sudo chmod -R 775 /var/www/spmi/storage /var/www/spmi/bootstrap/cache
   ```

6. **Konfigurasi Web Server (Nginx)**
   Buat konfigurasi *virtual host*: `sudo nano /etc/nginx/sites-available/spmi`
   ```nginx
   server {
       listen 80;
       server_name domain-spmi-anda.ac.id;
       root /var/www/spmi/public;

       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";

       index index.php;
       charset utf-8;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location = /favicon.ico { access_log off; log_not_found off; }
       location = /robots.txt  { access_log off; log_not_found off; }

       error_page 404 /index.php;

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```
   Aktifkan *site* dan *restart* Nginx:
   ```bash
   sudo ln -s /etc/nginx/sites-available/spmi /etc/nginx/sites-enabled/
   sudo systemctl restart nginx
   ```
   Aplikasi Anda kini sudah *live* dan dapat diakses publik!

---

## 2. Deployment di Server Lokal (Intranet Kampus / On-Premise)

Metode ini cocok apabila institusi ingin menyimpan data secara mandiri dan sistem hanya diakses oleh pengguna yang terhubung di jaringan Wi-Fi/Intranet kampus tanpa terekspos internet publik.

### Langkah-langkah menggunakan XAMPP/Laragon (Windows Server)
1. **Persiapan Folder:** Salin/clone seluruh folder proyek ke dalam direktori root server (`C:\xampp\htdocs\spmi` atau `C:\laragon\www\spmi`).
2. **Build Aset:** Buka Command Prompt di dalam folder proyek tersebut, jalankan `composer install` dan `npm install && npm run build`.
3. **Konfigurasi DB:** Buka phpMyAdmin lokal (`http://localhost/phpmyadmin`) dan buat database baru.
4. **Environment:** Ubah `.env` dan masukkan kredensial koneksi MariaDB/MySQL lokal. Jalankan `php artisan key:generate` dan `php artisan migrate --seed`.
5. **Konfigurasi Akses Jaringan:** 
   - Bila Anda menggunakan XAMPP, buka file `httpd-vhosts.conf` dan arahkan *DocumentRoot* virtual host Anda ke `C:/xampp/htdocs/spmi/public`.
   - Di komputer klien (komputer dosen/staf), akses sistem via *browser* dengan mengetik alamat IP server lokal tersebut (contoh: `http://192.168.1.50`).

---

## 3. Deployment di Shared Hosting (cPanel)

Deploy framework SPA seperti Inertia ke Shared Hosting seringkali terkendala akses terminal yang dibatasi. Namun Anda tetap dapat mengakalinya dengan proses *pre-build*.

1. **Lakukan Build Secara Lokal Dahulu**
   Pastikan di laptop Anda sudah dipastikan bekerja dengan baik, jalankan perintah kompilasi produksi:
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   ```

2. **Upload Folder ke cPanel**
   - Kompres seluruh isi folder project ke bentuk file `.zip` (KECUALI folder `node_modules` karena sudah tidak dipakai setelah proses build selesai).
   - Di dashboard cPanel, masuk ke menu **File Manager**.
   - Unggah file `.zip` Anda ke direktori yang tidak dapat diakses publik (jangan taruh di dalam `public_html`). Misalkan buat folder `spmi_core` di root direktori lalu unggah dan ekstrak ke situ (`home/username/spmi_core`).

3. **Mengarahkan Akses Publik (Symlink)**
   - Pindahkan *seluruh isi* folder `home/username/spmi_core/public` ke dalam folder `public_html` (atau subdomain Anda).
   - Buka file `index.php` yang sekarang berada di dalam `public_html` (edit).
   - Perbaiki baris perintah `require` agar menunjuk ke jalur (`path`) sistem *core* yang asli:
     ```php
     require __DIR__.'/../spmi_core/vendor/autoload.php';
     $app = require_once __DIR__.'/../spmi_core/bootstrap/app.php';
     ```

4. **Konfigurasi Database & Keamanan**
   - Buat database dan hak pengguna MySQL di cPanel lewat menu **MySQL Databases**.
   - Buka file `.env` di dalam direktori `spmi_core`, sesuaikan variabel koneksinya dan pastikan `APP_ENV=production`.
   
5. **Penyimpanan Berkas (Storage Link)**
   Agar dokumen/file yang diunggah terbaca secara publik, masuk ke terminal cPanel dan buat symlink:
   ```bash
   php artisan storage:link
   ```
   Atau Anda bisa menggunakan *Cron Jobs* / kode PHP sederhana di `web.php` untuk memanggil perintah tersebut apabila Shared Hosting Anda tidak mengizinkan akses SSH/Terminal.
