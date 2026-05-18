# RENCANA PENINGKATAN (UPGRADE) SISTEM e-SPMI

## 1. RINGKASAN EKSEKUTIF

Laporan ini menyajikan hasil audit mendalam terhadap seluruh sistem aplikasi **e-SPMI** yang dibangun dengan stack Laravel, Vue 3, dan Inertia.js. Melalui analisis kesenjangan (*gap analysis*), evaluasi alur kerja, dan komparasi dengan standar sistem penjaminan mutu perguruan tinggi nasional, laporan ini merumuskan **22 rekomendasi peningkatan** yang dikelompokkan ke dalam 8 kategori utama.

Tujuan utama dari usulan peningkatan ini adalah untuk mentransformasi e-SPMI dari sekadar aplikasi pencatatan manual menjadi sebuah platform **Penjaminan Mutu Cerdas Berbasis Data (*Data-Driven Smart Quality Assurance*)** yang mendukung otomatisasi, integrasi ekosistem kampus, visualisasi eksekutif, dan kepatuhan standar keamanan siber tingkat tinggi.

### Matriks Rekomendasi Prioritas (Quick Wins & High Impact)

Dari 22 usulan yang dirumuskan, berikut adalah 5 rekomendasi prioritas utama yang sangat disarankan untuk diimplementasikan terlebih dahulu karena memberikan dampak instan dengan tingkat kompleksitas yang dapat dikelola (*Quick Wins*):

| Kategori | Usulan Peningkatan | Dampak Utama | Kompleksitas | Prioritas |
|---|---|---|---|---|
| **Keamanan** | UI Panel Pengaktifan Two-Factor Authentication (2FA) | Mencegah pembajakan akun pimpinan dan auditor | **Sedang** | P1 (Tertinggi) |
| **Audit Trail** | Administrator Activity Log Viewer Dashboard | Transparansi penuh audit kepatuhan aktivitas sistem | **Sedang** | P1 (Tertinggi) |
| **Otomasi** | Sistem Pengingat (Reminder) Otomatis via Email & WhatsApp | Meningkatkan ketepatan waktu pengisian ED dan tindak lanjut | **Sedang** | P2 (Tinggi) |
| **Visualisasi** | Radar Chart (Spider Chart) Ketercapaian Standar Mutu | Evaluasi visual instan kekuatan dan kelemahan standar prodi | **Sedang** | P2 (Tinggi) |
| **UX/UI** | Bulk Actions (Tindakan Massal) pada Manajemen Dokumen & Temuan | Efisiensi kerja admin dalam mengelola ratusan entri | **Rendah** | P3 (Sedang) |

---

## 2. BAGIAN 1 — FITUR YANG UMUM DIMILIKI SISTEM SEJENIS (GAP ANALYSIS)

Membandingkan e-SPMI dengan sistem penjaminan mutu internal kampus-kampus besar secara nasional, terdapat beberapa modul krusial yang saat ini belum diakomodasi oleh sistem.

### 1. Modul Simulasi & Prediksi Peringkat Akreditasi (BAN-PT & LAM)
* **Deskripsi**: Fitur kalkulator simulasi instan yang memetakan skor ketercapaian indikator SPMI ke dalam instrumen akreditasi resmi BAN-PT atau LAM (Lembaga Akreditasi Mandiri seperti LAM-INFOKOM, LAMEMBA, dll). Sistem akan secara otomatis menghitung prediksi predikat akreditasi prodi (Unggul / Sangat Baik / Baik).
* **Manfaat**: Program studi (Auditee) dapat secara proaktif memantau kesiapan akreditasi mereka jauh sebelum siklus AMI eksternal dimulai.
* **Estimasi Kompleksitas**: **Tinggi** (Memerlukan pemetaan rumus skor dan kriteria khusus BAN-PT/LAM yang dinamis).
* **Penerima Manfaat Utama**: Auditee (Ketua Prodi), Fakultas, Rektorat.

### 2. Ruang Kolaborasi Pengendalian Rapat Tinjauan Manajemen (RTM Workspace)
* **Deskripsi**: Menyediakan ruang kerja kolaboratif digital (*digital workspace*) khusus untuk pelaksanaan RTM. Di dalamnya mencakup pencatatan risalah rapat otomatis, absensi digital peserta rapat, pengunggahan dokumentasi foto/video RTM, dan konversi butir kesepakatan RTM menjadi tugas ber-deadline langsung di sistem.
* **Manfaat**: Memastikan seluruh temuan AMI yang dibawa ke forum RTM memiliki bukti tindak lanjut formal yang terdokumentasi dan transparan secara hukum penjaminan mutu.
* **Estimasi Kompleksitas**: **Sedang** (Memerlukan fitur manajemen tugas sederhana dan editor teks kolaboratif).
* **Penerima Manfaat Utama**: Admin, Auditor (Ketua Lembaga Penjaminan Mutu), Auditee.

### 3. Modul Portofolio & Kompetensi Auditor
* **Deskripsi**: Sistem manajemen data auditor yang melacak riwayat sertifikat kompetensi (misal: Auditor Bersertifikat BAN-PT/Kemenristekdikti), beban kerja audit aktif (*audit workload*), dan riwayat program studi yang pernah diaudit untuk mencegah konflik kepentingan (*conflict of interest*).
* **Manfaat**: Memudahkan pimpinan mendistribusikan penugasan audit AMI secara objektif, kompeten, dan adil.
* **Estimasi Kompleksitas**: **Sedang** (Memerlukan formulir pengelolaan profil auditor dan verifikasi dokumen).
* **Penerima Manfaat Utama**: Admin (LP3M/LPM), Auditor.

---

## 3. BAGIAN 2 — PENINGKATAN FITUR YANG SUDAH ADA

Optimalisasi fitur-fitur yang sudah berjalan untuk meningkatkan produktivitas operasional harian.

### 1. Kontrol Versi Dokumen Mutu (Document Versioning)
* **Deskripsi**: Daripada menimpa (*overwrite*) atau menghapus berkas dokumen lama ketika ada pembaruan dokumen standar/kebijakan mutu, sistem harus mendukung riwayat versi (Revisi 0, Revisi 1, dst).
* **Manfaat**: Mempertahankan rekam jejak evolusi dokumen mutu perguruan tinggi untuk keperluan visitasi akreditasi eksternal yang sering menuntut bukti historis dokumen.
* **Estimasi Kompleksitas**: **Sedang** (Memerlukan relasi tabel one-to-many untuk versi file).
* **Penerima Manfaat Utama**: Semua Pengguna.

### 2. PDF Previewer Terintegrasi (In-App PDF Viewer)
* **Deskripsi**: Menampilkan isi dokumen PDF langsung di dalam antarmuka aplikasi menggunakan library seperti `PDF.js` atau previewer native iframe bergaya modern, tanpa harus memaksa browser mengunduh file atau membuka tab kosong baru.
* **Manfaat**: Mempercepat proses peninjauan bukti dukung oleh Auditor selama proses *Desk Evaluation* karena tidak perlu berpindah jendela browser atau menumpuk file unduhan di komputer lokal.
* **Estimasi Kompleksitas**: **Sedang** (Integrasi library Javascript previewer PDF di dalam Vue 3 Modal).
* **Penerima Manfaat Utama**: Auditor, Fakultas, Auditee.

### 3. Panel Pencarian & Filter Multi-Kolom Tingkat Lanjut (Advanced Search Filter)
* **Deskripsi**: Menyediakan pencarian global elastis yang mendukung filter silang instan, seperti menyaring dokumen berdasarkan rentang tahun, kategori, status approval, dan tipe auditee sekaligus secara asinkronus (Reactive/Live Filter).
* **Manfaat**: Memotong waktu pencarian dokumen di repositori yang memiliki ribuan data menjadi hitungan detik.
* **Estimasi Kompleksitas**: **Rendah** (Optimasi kueri Eloquent dan penambahan reactive watcher di Vue 3).
* **Penerima Manfaat Utama**: Semua Pengguna.

---

## 4. BAGIAN 3 — DASHBOARD DAN VISUALISASI DATA

Transformasi dashboard saat ini yang didominasi oleh angka mentah menjadi pusat analisis visual yang komunikatif.

### 1. Visualisasi Radar Chart (Spider Chart) Ketercapaian Standar Mutu
* **Deskripsi**: Grafik radar interaktif pada dashboard Auditee dan Auditor yang menampilkan skor rata-rata ketercapaian per butir standar mutu (misal: Standar SDM, Standar Kurikulum, Standar Sarana Prasarana) dibandingkan dengan target universitas.
* **Manfaat**: Memberikan profil visual instan mengenai aspek kekuatan prodi dan aspek kelemahan yang memerlukan tindakan korektif segera.
* **Estimasi Kompleksitas**: **Sedang** (Menggunakan integrasi `Chart.js` atau `ApexCharts` pada Vue 3).
* **Penerima Manfaat Utama**: Auditee, Rektorat, Fakultas.

### 2. Heatmap Distribusi Temuan KTS (Ketiadaan Standar / Ketidaksesuaian)
* **Deskripsi**: Peta sebaran temuan berbasis warna (*heatmap*) yang memetakan jumlah temuan KTS kategori Mayor dan Minor lintas Fakultas atau Program Studi di universitas.
* **Manfaat**: Memudahkan pimpinan universitas melakukan intervensi taktis pada fakultas/prodi yang memiliki rapor merah/akumulasi temuan KTS terbanyak.
* **Estimasi Kompleksitas**: **Sedang** (Visualisasi matriks berkode warna interaktif).
* **Penerima Manfaat Utama**: Rektorat, Dekan Fakultas, LPM/LP3M.

### 3. Timeline Lini Masa Visual Siklus PPEPP
* **Deskripsi**: Widget grafis berbentuk diagram alir horizontal (*stepper timeline*) yang menampilkan status real-time siklus penjaminan mutu perguruan tinggi (Penetapan -> Pelaksanaan -> Evaluasi -> Pengendalian -> Peningkatan).
* **Manfaat**: Membantu seluruh civitas akademika melacak jadwal tenggat dan tahapan AMI aktif yang sedang berjalan saat ini.
* **Estimasi Kompleksitas**: **Rendah** (Pembuatan komponen Vue 3 stepper visual).
* **Penerima Manfaat Utama**: Semua Pengguna.

---

## 5. BAGIAN 4 — OTOMASI DAN EFISIENSI ALUR KERJA

Mengurangi beban administratif manual yang rentan terhadap keterlambatan pengisian data.

### 1. Sistem Reminder Otomatis via Email & WhatsApp
* **Deskripsi**: Integrasi penjadwalan berkala (*cron job scheduler*) yang mendeteksi batas waktu (*deadline*) pengisian Evaluasi Diri (ED), Desk Evaluation, atau penyelesaian Rencana Tindak Lanjut (RTL). Sistem akan otomatis mengirim notifikasi pengingat via Email dan gateway WhatsApp ke pihak yang belum menyelesaikan tugasnya.
* **Manfaat**: Mengeliminasi keterlambatan pengisian instrumen mutu tanpa membutuhkan staf LP3M untuk menghubungi koordinator prodi satu per satu secara manual.
* **Estimasi Kompleksitas**: **Sedang** (Konfigurasi Laravel Scheduler, Queue, dan integrasi API pihak ketiga seperti Twilio/Fonnte).
* **Penerima Manfaat Utama**: Admin, Auditee, Auditor.

### 2. Kunci Form & Auto-Submit Berdasarkan Batas Waktu Jadwal
* **Deskripsi**: Mekanisme yang secara otomatis mengunci lembar pengisian instrumen mutu Evaluasi Diri/Desk Evaluation dan mengubah statusnya menjadi `Submitted` secara otomatis begitu tanggal batas akhir terlampaui.
* **Manfaat**: Menjaga integritas dan disiplin waktu proses audit tanpa toleransi keterlambatan ilegal demi konsistensi jadwal tahunan universitas.
* **Estimasi Kompleksitas**: **Rendah** (Validasi tanggal pada level middleware/controller sebelum eksekusi aksi tulis database).
* **Penerima Manfaat Utama**: Admin, Auditor.

### 3. Otomatisasi Perhitungan Skor Kinerja Berbasis Indikator
* **Deskripsi**: Skor Evaluasi Diri dan Skor Desk Evaluation dihitung secara otomatis oleh sistem melalui akumulasi pengisian lembar butir indikator standar. Pengguna cukup memilih kriteria penilaian (1-4 atau A-E) pada form indikator, dan skor akhir rata-rata akan langsung dikalkulasikan di database.
* **Manfaat**: Menghilangkan kesalahan perhitungan matematis manusia (*human error*) dan manipulasi nilai manual di akhir siklus audit.
* **Estimasi Kompleksitas**: **Tinggi** (Memerlukan restrukturisasi skema database pengisian butir indikator).
* **Penerima Manfaat Utama**: Auditee, Auditor.

---

## 6. BAGIAN 5 — PELAPORAN DAN REKAP DATA

Menyediakan format pelaporan eksekutif yang siap pakai untuk kebutuhan rapat pimpinan maupun akreditasi.

### 1. Generator Dokumen Laporan Hasil Audit (LHA) PDF Resmi
* **Deskripsi**: Fitur ekspor satu-klik yang mengompilasi seluruh hasil AMI prodi tertentu (mencakup data umum, skor ketercapaian standar, rincian temuan KTS Mayor/Minor, rekomendasi auditor, dan tanda tangan digital terverifikasi) menjadi satu file PDF formal dengan kop surat institusi resmi.
* **Manfaat**: Memudahkan auditor mencetak dan mengarsipkan dokumen legal LHA untuk ditunjukkan pada asesor akreditasi eksternal.
* **Estimasi Kompleksitas**: **Sedang** (Menggunakan library `barryvdh/laravel-dompdf` atau `snappy`).
* **Penerima Manfaat Utama**: Auditor, Auditee, LPM/LP3M.

### 2. Ekspor Rekapitulasi Eksekutif Lintas Siklus Berformat Excel (Multi-Sheet)
* **Deskripsi**: Generator file Excel dinamis yang merangkum perbandingan performa ketercapaian mutu antar fakultas, daftar temuan yang masih berstatus Open/Closed, dan riwayat skor mutu dalam 3 siklus periode terakhir ke dalam lembar kerja terpisah (*multi-sheet*).
* **Manfaat**: Memungkinkan pimpinan melakukan analisis tren mutu jangka panjang menggunakan tools pengolah data eksternal secara leluasa.
* **Estimasi Kompleksitas**: **Sedang** (Menggunakan library `Maatwebsite/Laravel-Excel`).
* **Penerima Manfaat Utama**: Rektorat, Dekan Fakultas, Admin.

---

## 7. BAGIAN 6 — AKSESIBILITAS DAN PENGALAMAN PENGGUNA (UX)

Mempersingkat kurva pembelajaran pengguna baru dan meningkatkan efisiensi navigasi.

### 1. Fitur Tindakan Massal (Bulk Actions) pada Tabel
* **Deskripsi**: Penambahan checkbox pada baris tabel (terutama tabel Manajemen Dokumen dan Daftar Temuan) yang memungkinkan administrator melakukan aksi massal seperti menghapus terpilih, menyetujui terpilih, atau mengekspor entri terpilih dalam satu klik.
* **Manfaat**: Menghemat waktu operasional admin secara drastis saat mengelola ratusan data dokumen standar mutu baru.
* **Estimasi Kompleksitas**: **Rendah** (Modifikasi array pilihan di frontend Vue 3 dan endpoint bulk-update di Laravel).
* **Penerima Manfaat Utama**: Admin, Auditor.

### 2. Wisaya Pengenalan Interaktif (Interactive Onboarding Wisard)
* **Deskripsi**: Panduan tur virtual langkah-demi-langkah yang interaktif (misal menggunakan library `driver.js`) yang muncul saat pengguna pertama kali login dengan role tertentu. Wisik ini menyoroti dan menjelaskan fungsi dari setiap menu penting.
* **Manfaat**: Mengurangi beban pelatihan sistem aplikasi oleh LP3M kepada dosen-dosen baru atau kaprodi baru secara signifikan.
* **Estimasi Kompleksitas**: **Sedang** (Konfigurasi skrip tur interaktif driver di frontend Vue 3).
* **Penerima Manfaat Utama**: Pengguna Baru (Semua Role).

### 3. Modul FAQ, Unduh Panduan, & Helpdesk Terintegrasi
* **Deskripsi**: Halaman pusat bantuan terpadu di dalam portal aplikasi yang berisi direktori pertanyaan sering diajukan (FAQ), tombol unduh PDF manual aplikasi per role, dan formulir pengajuan keluhan teknis (*support ticket*) langsung ke admin sistem.
* **Manfaat**: Meminimalisir jalur komunikasi informal (seperti pesan WhatsApp pribadi ke pengembang) saat pengguna menemui kendala pengisian instrumen mutu.
* **Estimasi Kompleksitas**: **Rendah** (Pembuatan halaman statis FAQ dan formulir tiket sederhana).
* **Penerima Manfaat Utama**: Semua Pengguna.

---

## 8. BAGIAN 7 — INTEGRASI DAN KONEKTIVITAS

Membangun koneksi asinkronus ke ekosistem sistem informasi universitas guna mengeliminasi pengisian ganda.

### 1. Sinkronisasi Otomatis Data Akademik via API PDDikti
* **Deskripsi**: Integrasi dengan API Pangkalan Data Pendidikan Tinggi (PDDikti) Nasional untuk secara otomatis menarik indikator-indikator kuantitatif standar mutu, seperti rasio dosen terhadap mahasiswa, jumlah mahasiswa aktif, kelulusan tepat waktu, dan profil keahlian dosen.
* **Manfaat**: Auditee tidak perlu lagi mengunggah dokumen bukti dukung fisik atau mengetik ulang data statistik yang sebenarnya sudah dilaporkan secara resmi ke negara.
* **Estimasi Kompleksitas**: **Tinggi** (Memerlukan akses kredensial WS PDDikti Feeder dan pemetaan data yang kompleks).
* **Penerima Manfaat Utama**: Auditee, LPM/LP3M.

### 2. Integrasi Single Sign-On (SSO) Kampus Terpadu
* **Deskripsi**: Menghubungkan proses otentikasi login aplikasi e-SPMI dengan gerbang login Single Sign-On (SSO) universitas berbasis OAuth 2.0, SAML, atau LDAP.
* **Manfaat**: Dosen, Auditor, dan Dekan tidak perlu menghafal kata sandi baru khusus untuk e-SPMI; mereka cukup login menggunakan akun institusi resmi mereka yang sudah terintegrasi.
* **Estimasi Kompleksitas**: **Sedang** (Menggunakan library `Socialite` Laravel atau konfigurasi protokol SAML/LDAP).
* **Penerima Manfaat Utama**: Semua Civitas Academica.

### 3. Konektivitas dengan Sistem Informasi Kepegawaian (SIMPEG) Kampus
* **Deskripsi**: Sinkronisasi data kepegawaian dosen untuk mendeteksi secara otomatis jabatan fungsional (Lektor Kepala, Guru Besar), sertifikat dosen (Serdos), kompetensi profesional, serta rekam jejak penelitian (terintegrasi dengan SINTA).
* **Manfaat**: Mempermudah proses audit standar SDM Dosen secara instan dan bebas manipulasi karena data bersumber langsung dari sistem HRD resmi.
* **Estimasi Kompleksitas**: **Tinggi** (Memerlukan kerjasama API lintas departemen IT internal kampus).
* **Penerima Manfaat Utama**: Admin, Auditee.

---

## 9. BAGIAN 8 — KEAMANAN DAN AUDIT TRAIL

Melindungi data sensitif penjaminan mutu dan menjamin transparansi operasional sistem secara hukum.

### 1. Antarmuka UI Pengaktifan Two-Factor Authentication (2FA)
* **Deskripsi**: Menyediakan panel khusus di halaman Profil pengguna yang memungkinkan mereka mengaktifkan autentikasi dua faktor (menggunakan One-Time Password / OTP berbasis aplikasi seperti Google Authenticator atau Microsoft Authenticator). Fitur ini sebenarnya didukung di backend (Fortify) tetapi belum diekspos di frontend.
* **Manfaat**: Menghindari potensi sabotase atau manipulasi nilai audit oleh pihak ketiga yang berhasil menebak kata sandi pengguna (khususnya akun pimpinan atau auditor).
* **Estimasi Kompleksitas**: **Sedang** (Pembuatan antarmuka visual QR Code, input kode konfirmasi, dan manajemen recovery codes di Vue 3).
* **Penerima Manfaat Utama**: Semua Pengguna (Sangat Direkomendasikan untuk Admin dan Auditor).

### 2. Dasbor Pemantau Log Aktivitas Administrator (Activity Log Viewer)
* **Deskripsi**: Antarmuka dasbor interaktif khusus administrator untuk melihat, memfilter, dan mencari rekam jejak log aktivitas yang tercatat di tabel `activity_log` (didukung oleh Spatie Activitylog). Log mencakup informasi siapa, kapan, alamat IP, peramban browser, dan detail modifikasi data sebelum/sesudah perubahan.
* **Manfaat**: Memberikan kemampuan forensik penuh bagi sistem penjaminan mutu agar jika ada perselisihan data nilai mutu atau penghapusan dokumen secara tidak sah, admin dapat langsung mengidentifikasi pelakunya secara valid.
* **Estimasi Kompleksitas**: **Sedang** (Pembuatan UI visualisasi log, filter log, dan pagination khusus Admin).
* **Penerima Manfaat Utama**: Admin Utama (Lembaga Penjaminan Mutu).

### 3. Kebijakan Kedaluwarsa Kata Sandi (Password Expiry Policy)
* **Deskripsi**: Menambahkan kebijakan keamanan yang memaksa pengguna mengganti kata sandi mereka secara berkala setiap 90 atau 180 hari sekali, serta menerapkan validasi yang melaramg penggunaan 3 kata sandi terakhir.
* **Manfaat**: Meminimalisir risiko kebocoran data jangka panjang akibat kata sandi lama yang bocor di platform lain atau terekspos di browser lokal.
* **Estimasi Kompleksitas**: **Rendah** (Penambahan kolom tanggal `password_changed_at` di tabel users dan pemeriksaan middleware).
* **Penerima Manfaat Utama**: Semua Pengguna.
