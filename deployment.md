# Panduan Deployment eSPMI

Dokumentasi ini berisi langkah-langkah untuk mendeploy aplikasi eSPMI (berbasis Laravel + Vue Inertia) ke berbagai jenis environment server di level produksi.

---

## 1. Deployment di VPS (Virtual Private Server) Ubuntu

Ini adalah metode yang paling disarankan untuk mendapatkan performa tinggi, keamanan server mandiri, dan kontrol maksimal atas aplikasi.

### Prasyarat
- VPS dengan sistem operasi Ubuntu 22.04 / 24.04 LTS
- Akses root / user sudo
- Domain yang sudah diarahkan ke alamat IP VPS (A Record)

### Langkah-langkah
1. **Update Sistem & Instalasi Nginx, PHP 8.4, MariaDB, Node.js**
   Buka terminal SSH Anda dan jalankan perintah berikut untuk memasang semua kebutuhan *stack* sistem:
   ```bash
   sudo apt update && sudo apt upgrade -y
   sudo apt install nginx mariadb-server curl git unzip software-properties-common -y

   # Setup repository PHP 8.4
   sudo add-apt-repository ppa:ondrej/php -y
   sudo apt update
   sudo apt install php8.4 php8.4-cli php8.4-fpm php8.4-mysql php8.4-xml php8.4-mbstring php8.4-curl php8.4-zip php8.4-bcmath php8.4-intl php8.4-gd -y

   # Setup Node.js v22 LTS
   curl -fsSL [https://deb.nodesource.com/setup_22.x](https://deb.nodesource.com/setup_22.x) | sudo -E bash -
   sudo apt install -y nodejs
   ```

2. **Instalasi Composer (Package Manager PHP)**
   ```bash
   curl -sS [https://getcomposer.org/installer](https://getcomposer.org/installer) | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

3. **Setup Database MariaDB**
   Masuk ke konsol MariaDB dan buat database beserta user khusus untuk eSPMI:
   ```bash
   sudo mariadb -u root
   CREATE DATABASE spmi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'spmi_user'@'localhost' IDENTIFIED BY 'password_kuat_anda';
   GRANT ALL PRIVILEGES ON spmi_db.* TO 'spmi_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

4. **Kloning & Konfigurasi Aplikasi**
   Letakkan aplikasi di direktori `/var/www`:
   ```bash
   cd /var/www
   sudo git clone [https://github.com/rwbu69/spmi-product.git](https://github.com/rwbu69/spmi-product.git) spmi
   sudo chown -R $USER:www-data /var/www/spmi
   cd spmi

   # Install dependensi (optimasi produksi)
   composer install --optimize-autoloader --no-dev
   npm ci
   npm run build

   # Setup Environment
   cp .env.example .env
   php artisan key:generate
   ```
   *Penting: Buka file `.env` dengan `nano .env`, lalu ubah `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-spmi-anda.ac.id`, `DB_CONNECTION=mariadb`, serta isikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai data pada langkah 3.*

   Jalankan migrasi database, buat *symlink* penyimpanan berkas, dan optimasi cache produksi:
   ```bash
   php artisan migrate --force --seed
   php artisan storage:link
   php artisan optimize
   ```

5. **Pengaturan Hak Akses Folder (Permissions)**
   Web server membutuhkan akses penulisan untuk menyimpan sesi, log, dan unggahan file dokumen:
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
       client_max_body_size 50M;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location = /favicon.ico { access_log off; log_not_found off; }
       location = /robots.txt  { access_log off; log_not_found off; }

       error_page 404 /index.php;

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```
   Aktifkan *site*, uji konfigurasi, dan *restart* Nginx:
   ```bash
   sudo ln -s /etc/nginx/sites-available/spmi /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl restart nginx
   ```

7. **Aktifkan HTTPS / SSL (Direkomendasikan)**
   Pasang sertifikat SSL gratis dari Let's Encrypt menggunakan Certbot agar koneksi aman:
   ```bash
   sudo apt install certbot python3-certbot-nginx -y
   sudo certbot --nginx -d domain-spmi-anda.ac.id
   ```
   Aplikasi Anda kini sudah *live* dan dapat diakses publik melalui HTTPS!

---

## 2. Deployment di Server Lokal (Intranet Kampus / On-Premise)

Metode ini cocok apabila institusi ingin menyimpan data secara mandiri dan sistem hanya diakses oleh pengguna yang terhubung di jaringan Wi-Fi/Intranet kampus tanpa terekspos internet publik.

### Langkah-langkah menggunakan Laragon / XAMPP (Windows Server)
1. **Pastikan Versi PHP 8.4:** Gunakan PHP 8.4 dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`, dan `zip` aktif di `php.ini`.
2. **Persiapan Folder:** Salin/clone seluruh folder proyek ke dalam direktori root server (`C:\laragon\www\spmi` atau `C:\xampp\htdocs\spmi`).
3. **Build Aset:** Buka terminal di dalam folder proyek tersebut, lalu jalankan:
   ```powershell
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   ```
4. **Konfigurasi Database:** Buka HeidiSQL / phpMyAdmin lokal dan buat database baru bernama `spmi_project`.
5. **Environment & Migrasi:** Salin `.env.example` menjadi `.env`, sesuaikan `DB_CONNECTION=mariadb` beserta kredensial database lokal, lalu jalankan:
   ```powershell
   php artisan key:generate
   php artisan migrate --force --seed
   php artisan storage:link
   ```
6. **Konfigurasi Akses Jaringan:**
   - Bila menggunakan **Laragon**, aktifkan *Virtual Host* otomatis dan pastikan layanan Apache/Nginx terbuka pada firewall jaringan lokal.
   - Bila menggunakan **XAMPP**, buka `httpd-vhosts.conf` dan arahkan `DocumentRoot` virtual host ke `C:/xampp/htdocs/spmi/public`.
   - Di komputer klien (komputer dosen/staf), akses sistem via *browser* menggunakan alamat IP server lokal tersebut (contoh: `http://192.168.1.50`).

---

## 3. Deployment di Shared Hosting (cPanel)

Deploy framework SPA seperti Inertia ke Shared Hosting sering kali terkendala akses terminal atau memori build yang terbatas. Namun Anda dapat mengakalinya dengan proses *pre-build* di komputer lokal.

1. **Pastikan Versi PHP di cPanel**
   Masuk ke menu **Select PHP Version** atau **MultiPHP Manager** di cPanel, lalu pilih **PHP 8.4** dan pastikan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`, dan `zip` dalam keadaan aktif.

2. **Lakukan Build Secara Lokal Dahulu**
   Pastikan di komputer lokal Anda proyek berjalan dengan baik, lalu jalankan kompilasi produksi:
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   ```

3. **Upload Folder ke cPanel**
   - Kompres seluruh isi folder proyek menjadi file `.zip` (**kecuali** folder `node_modules` karena sudah tidak diperlukan setelah proses `npm run build` selesai).
   - Di dashboard cPanel, masuk ke menu **File Manager**.
   - Unggah file `.zip` tersebut ke direktori yang tidak dapat diakses publik (jangan taruh langsung di dalam `public_html`). Buat folder `spmi_core` di root direktori (`/home/username/spmi_core`), lalu unggah dan ekstrak file `.zip` ke dalamnya.

4. **Mengarahkan Akses Publik**
   - Pindahkan *seluruh isi* folder `/home/username/spmi_core/public` ke dalam folder `public_html` (atau folder subdomain Anda).
   - Buka file `index.php` yang sekarang berada di dalam `public_html` (klik **Edit**).
   - Ubah baris `require` agar menunjuk ke jalur (`path`) folder `spmi_core`:
     ```php
     require __DIR__.'/../spmi_core/vendor/autoload.php';
     $app = require_once __DIR__.'/../spmi_core/bootstrap/app.php';
     ```

5. **Konfigurasi Database & Environment**
   - Buat database dan user MySQL/MariaDB di cPanel melalui menu **Manage My Databases** (atau **MySQL Databases**), lalu berikan hak akses penuh (*All Privileges*).
   - Buka file `.env` di dalam direktori `spmi_core`, atur `APP_ENV=production`, `APP_DEBUG=false`, dan sesuaikan kredensial database.

6. **Penyimpanan Berkas (Storage Link)**
   Agar dokumen/file yang diunggah terbaca secara publik, masuk ke menu **Terminal** di cPanel dan jalankan:
   ```bash
   cd ~/spmi_core
   php artisan storage:link
   ```
   *(Apabila paket Shared Hosting Anda tidak menyediakan akses SSH/Terminal, buat symlink melalui Cron Jobs cPanel dengan perintah `ln -s /home/username/spmi_core/storage/app/public /home/username/public_html/storage`, lalu hapus cron job tersebut setelah berjalan).*