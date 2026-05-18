# LAPORAN AUDIT & ANALISIS SISTEM E-SPMI
## (Enterprise Quality, Security, and Performance Audit)

---

### DAFTAR ISI
1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Matriks Temuan Audit](#2-matriks-temuan-audit)
3. [Bagian 1 — Keamanan (Security)](#bagian-1--keamanan-security)
4. [Bagian 2 — Performa (Performance)](#bagian-2--performa-performance)
5. [Bagian 3 — Kualitas Kode (Code Quality)](#bagian-3--kualitas-kode-code-quality)
6. [Bagian 4 — Struktur & Arsitektur](#bagian-4--struktur--arsitektur)
7. [Bagian 5 — Pengalaman Pengguna (UX)](#bagian-5--pengalaman-pengguna-ux)
8. [Bagian 6 — Konsistensi Fitur Antar Role](#bagian-6--konsistensi-fitur-antar-role)
9. [Rencana Aksi & Implementasi Perbaikan](#rencana-aksi--implementasi-perbaikan)

---

### 1. RINGKASAN EKSEKUTIF

Audit ini dilakukan secara menyeluruh terhadap seluruh codebase sistem aplikasi e-SPMI berbasis Laravel + Inertia.js + Vue.js. Fokus utama audit meliputi aspek **Keamanan**, **Performa**, **Kualitas Kode**, **Struktur Arsitektur**, **Pengalaman Pengguna (UX)**, dan **Konsistensi Peran (Role)**.

Secara umum, aplikasi ini telah dirancang dengan sangat baik memanfaatkan teknologi modern seperti Inertia.js dan Vue 3 TypeScript, serta didukung keamanan bawaan Laravel (seperti CSRF Protection dan mitigasi SQL Injection yang sangat solid). Namun, terdapat beberapa temuan kritis dan tinggi (seperti **data leakage pada dashboard Auditee**, **query N+1 yang berulang secara masif**, **kerentanan crash pada eager loading null-object**, serta **tidak adanya feedback error validasi di frontend**) yang memerlukan perhatian segera. 

Laporan ini menyajikan analisis mendalam dari setiap temuan beserta contoh kode (sebelum dan sesudah perbaikan) untuk memudahkan tim developer melakukan remediasi.

---

### 2. MATRIKS TEMUAN AUDIT

Berikut adalah tabel matriks ringkasan temuan audit yang dikelompokkan berdasarkan tingkat urgensi/prioritas:

| ID | Kategori | Temuan Utama | Lokasi | Prioritas | Dampak |
|---|---|---|---|---|---|
| **SEC-01** | Keamanan | Route `/dashboard` tidak dilindungi middleware role | `routes/spmi.php` | **Tinggi** | Pengguna baru tanpa role dapat mengakses halaman dashboard. |
| **SEC-02** | Keamanan | Kebocoran menu navigasi admin untuk pengguna tanpa role | `AppSidebar.vue` | **Tinggi** | Menu Admin terekspos secara visual di sidebar jika user tidak memiliki role. |
| **SEC-03** | Keamanan | Tidak ada penampil pesan error validasi di frontend | `ManajemenDokumen/Index.vue`, `EvaluasiDiri/Index.vue` | **Tinggi** | User bingung karena form menolak disave tanpa alasan yang jelas. |
| **PERF-01**| Performa | Query N+1 pada data pagination pengguna backoffice | `PenggunaBackofficeController.php` | **Tinggi** | Beban database melonjak seiring bertambahnya jumlah user backoffice. |
| **PERF-02**| Performa | Query N+1 pada data standar mutu bertingkat (recursive) | `StandarMutuController.php`, `RekapDeskEvalController.php` | **Tinggi** | N+1 query berulang terjadi saat memuat sub-standar level > 1 di frontend. |
| **PERF-03**| Performa | Query notifikasi sistem berjalan pada *setiap request* | `SystemNotificationService.php`, `HandleInertiaRequests.php` | **Sedang** | Perlambatan navigasi di seluruh aplikasi karena query JSON un-indexed. |
| **PERF-04**| Performa | Query berulang status periode aktif | `HandleInertiaRequests.php` | **Sedang** | Database menerima query identik berulang pada setiap navigasi halaman. |
| **PERF-05**| Performa | Kolom filter dan pengurutan kritis tidak memiliki index DB | `users`, `manajemen_dokumen`, `tahun_periode` | **Sedang** | Penurunan performa database secara eksponensial saat data bertambah. |
| **CODE-01**| Kualitas Kode| Potensi crash (Null-Object Reference Error) | `DeskEvaluationAuditorController.php` | **Tinggi** | Aplikasi crash total jika ada relasi `auditee` yang null pada evaluasi diri. |
| **CODE-02**| Kualitas Kode| Pelanggaran pemisahan perhatian (logika ekspor di Controller) | `DaftarTemuanController.php`, `DaftarKesesuaianController.php` | **Sedang** | Duplikasi logika mapping dan pelanggaran prinsip Single Responsibility. |
| **ARCH-01**| Arsitektur | Penggunaan validasi inline secara penuh (tanpa Form Requests) | Seluruh controller transaksi bisnis utama | **Sedang** | Controller gemuk (fat) dan logika validasi sulit digunakan kembali. |
| **ARCH-02**| Arsitektur | Absennya Laravel Policy pada sebagian besar model utama | Modul `Pelaksanaan`, `Ami`, `Pengendalian` | **Sedang** | Logika otorisasi tersebar di controller, menyulitkan maintenance. |
| **CONS-01**| Konsistensi | **Kebocoran Data (Data Leakage) skala global pada Auditee** | `BerandaController.php` | **Kritis** | Auditee dapat melihat statistik rata-rata nilai mutu program studi/unit lain. |

---

### BAGIAN 1 — KEAMANAN (SECURITY)

#### SEC-01: Endpoint `/dashboard` Tanpa Perlindungan Middleware Role
* **Deskripsi:** Route `/dashboard` hanya menggunakan middleware `['auth', 'verified']`. Tidak ada pembatasan role di tingkat route maupun controller.
* **Potensi Bahaya:** Setiap pengguna yang berhasil registrasi (terutama jika registrasi publik aktif via Fortify) dapat mengakses dashboard utama.
* **Rekomendasi:** Terapkan pembatasan role agar pengguna tanpa peran tidak diizinkan masuk ke area utama aplikasi.

#### SEC-02: Kebocoran Navigasi Admin di Sidebar (Fallback Role)
* **Deskripsi:** Pada komponen `AppSidebar.vue`, computed property `navGroups` memiliki fallback berupa `adminNavGroups` jika pengguna tidak memiliki role yang cocok:
  ```typescript
  const navGroups = computed<NavGroup[]>(() => {
      if (isAdmin.value) return adminNavGroups;
      if (isAuditor.value) return auditorNavGroups;
      ...
      return adminNavGroups; // fallback berbahaya
  });
  ```
* **Potensi Bahaya:** Pengguna publik tanpa role (misal pendaftar baru) akan melihat seluruh menu administrasi sistem (Tahun Periode, Pengaturan Pengguna, Log Sistem). Meskipun backend menolak akses (403), hal ini merusak estetika dan membeberkan rancangan sistem kepada pihak luar.
* **Rekomendasi:** Kembalikan array kosong `[]` sebagai fallback.

#### SEC-03: SQL Injection (Hasil Analisis: AMAN)
* **Deskripsi:** Seluruh database query di sistem ini sudah menggunakan Eloquent ORM secara konsisten dan parametrik (menggunakan Eloquent method seperti `where()`, `whereHas()`, dll.).
* **Hasil Audit:** **Sangat Baik**. Tidak ditemukan query SQL mentah (`DB::raw()`) yang rentan terhadap SQL Injection. Parameter binding berjalan optimal.

#### SEC-04: Validasi File Upload (Hasil Analisis: AMAN)
* **Deskripsi:** Validasi file di `ManajemenDokumenController.php` dan `UploadLaporanAmiController.php` berjalan dengan baik di sisi backend:
  ```php
  'file' => ['required', 'file', 'mimes:pdf', 'max:7168']
  ```
* **Hasil Audit:** **Sangat Baik**. Tipe file dibatasi secara ketat hanya dokumen `.pdf` dengan ukuran maksimum 7 MB (7168 KB), mencegah ancaman eksekusi file berbahaya (seperti shell php).

---

### BAGIAN 2 — PERFORMA (PERFORMANCE)

#### PERF-01: Query N+1 pada Transformasi Data Pengguna Backoffice
* **Deskripsi:** Pada `PenggunaBackofficeController.php`, relasi `auditeePusat` tidak dimuat menggunakan *eager loading*. Akibatnya, di dalam loop transformasi data pagination, sistem memicu query database baru untuk setiap user:
  ```php
  $data->getCollection()->transform(fn ($user) => [
      ...
      'unit' => $user->auditee_pusat_id ? "[".AuditeePusat::find($user->auditee_pusat_id)->nama."]" : '-'
  ]);
  ```
  Jika ada 20 user per halaman, ini memicu **20 query tambahan** ke tabel `auditee_pusat`.
* **Rekomendasi:** Gunakan `with(['roles', 'auditeePusat'])` saat mengambil data pengguna.

#### PERF-02: Query N+1 Bertingkat pada Eager Loading Standar Mutu
* **Deskripsi:** Di `StandarMutuController.php`, model `StandarMutu` mendefinisikan relasi rekursif `childrenRecursive`. Namun, pada pemanggilan query index:
  ```php
  $query = StandarMutu::with(['lembagaAkreditasi', 'tahunPeriode', 'childrenRecursive.indikator', 'indikator'])
  ```
  Eager loading `childrenRecursive.indikator` hanya bekerja pada anak tingkat 1. Jika pohon standar mutu memiliki kedalaman level 2 (Sub-Standar) atau level 3 (Butir), indikator mereka tidak termuat secara instan dan memicu query database baru per baris ketika diakses di frontend.
* **Rekomendasi:** Deklarasikan kedalaman sub-relasi secara eksplisit pada query `with`:
  ```php
  'childrenRecursive.indikator',
  'childrenRecursive.childrenRecursive.indikator',
  'childrenRecursive.childrenRecursive.childrenRecursive.indikator'
  ```

#### PERF-03: Query Notifikasi Sistem Berjalan pada *Setiap Request*
* **Deskripsi:** Notifikasi pengguna dimuat secara global melalui middleware `HandleInertiaRequests.php` di shared props:
  ```php
  $payload = $service->buildForUser($request->user());
  ```
  Ini memicu query pencarian notifikasi berbasis JSON (`data->menu_key`) pada tabel `notifications` untuk setiap navigasi/interaksi halaman. Filter JSON pada tabel besar tanpa index virtual dapat menyebabkan degradasi performa yang parah.
* **Rekomendasi:** Terapkan caching berdurasi singkat (misal 5 menit) untuk list notifikasi dan jumlah unread, atau gunakan Inertia Lazy Evaluation agar data notifikasi hanya di-load saat frontend secara eksplisit membutuhkannya.

#### PERF-04: Caching Periode Aktif
* **Deskripsi:** Sistem mengambil tahun periode aktif di shared props `HandleInertiaRequests.php` pada setiap request:
  ```php
  $aktif = \App\Models\TahunPeriode::where('status', 'Aktif')->first();
  ```
* **Rekomendasi:** Gunakan Laravel Cache dengan TTL 24 jam dan lakukan pembersihan cache (`Cache::forget`) ketika data di tabel `tahun_periode` ditambahkan, diubah, atau dihapus.

---

### BAGIAN 3 — KUALITAS KODE (CODE QUALITY)

#### CODE-01: Potensi Crash pada Eager Loading Null-Object (Auditor Dashboard)
* **Deskripsi:** Di `DeskEvaluationAuditorController.php`, terdapat query pengelompokan auditee berdasarkan periode:
  ```php
  $auditeeByPeriode = \App\Models\EvaluasiDiri::with('auditee')
      ->select('pengaturan_periode_id', 'auditee_id')
      ->distinct()
      ->get()
      ->groupBy('pengaturan_periode_id')
      ->map(fn ($group) => $group->map(fn ($e) => [
          'id'          => $e->auditee->id,          // RENTAN CRASH
          'nama_auditee' => $e->auditee->nama_auditee, // RENTAN CRASH
      ])->values());
  ```
  Jika relasi `auditee` mengembalikan `null` (misal karena ketidakkonsistenan data atau kegagalan relasi), baris di atas akan langsung memicu error **"Attempt to read property 'id' on null"** yang menyebabkan crash total pada halaman Desk Evaluation Auditor.
* **Rekomendasi:** Gunakan operator null-safe php (`?->`) atau filter record yang tidak memiliki relasi auditee:
  ```php
  'id'          => $e->auditee?->id,
  'nama_auditee' => $e->auditee?->nama_auditee ?? '-',
  ```

#### CODE-02: Pelanggaran Separation of Concerns pada Logika Ekspor Excel
* **Deskripsi:** Di `DaftarTemuanController.php` dan `DaftarKesesuaianController.php`, pemetaan data untuk ekspor Excel dilakukan langsung di dalam controller:
  ```php
  $data = [];
  foreach ($items as $i => $item) {
      $data[] = [ ... ]; // Logika penulisan array data Excel
  }
  ```
  Ini melanggar prinsip desain MVC dan Single Responsibility, karena logika presentasi data ekspor seharusnya didelegasikan sepenuhnya ke kelas Ekspor terkait (`DaftarTemuanExport`).
* **Rekomendasi:** Gunakan fitur `WithMapping` dari Laravel Excel di dalam kelas Export.

---

### BAGIAN 4 — STRUKTUR & ARSITEKTUR

#### ARCH-01: Logika Validasi Inline di Controller (Kurang Form Requests)
* **Deskripsi:** Hampir seluruh form di modul transaksi utama (seperti inisialisasi evaluasi diri, manajemen dokumen, input temuan, dll.) divalidasi langsung di dalam controller menggunakan `$request->validate([...])`. Hal ini membuat controller gemuk (fat controller) dan menyulitkan pengujian terisolasi (unit testing).
* **Rekomendasi:** Pindahkan validasi ke kelas Laravel Form Request khusus di `app/Http/Requests`.

#### ARCH-02: Absennya Otorisasi Terpusat (Laravel Policies)
* **Deskripsi:** Otorisasi hak akses (seperti siapa yang boleh mengedit/menghapus temuan atau evaluasi diri) dilakukan secara inline di controller menggunakan pengecekan manual:
  ```php
  if (!auth()->user()->hasRole('Admin')) { abort(403); }
  ```
  Model Policy hanya terdefinisi pada `ManajemenDokumen` dan `LaporanAmi`.
* **Rekomendasi:** Buatlah Policy class untuk setiap model inti (misal `EvaluasiDiriPolicy`, `DaftarTemuanPolicy`) dan panggil via `$this->authorize()`.

---

### BAGIAN 5 — PENGALAMAN PENGGUNA (UX)

#### UX-01: Tidak Ada Penampil Pesan Error Validasi di Frontend
* **Deskripsi:** Pada beberapa form penting di frontend, seperti form Tambah/Edit Dokumen (`ManajemenDokumen/Index.vue`) dan form Inisialisasi Evaluasi Baru (`EvaluasiDiri/Index.vue`), **tidak ada elemen penampil error** (`form.errors.field`).
* **Dampak:** Jika user mengirimkan data yang tidak valid (misal menduplikasi relasi periode-auditee yang unik, atau mengosongkan nama dokumen), backend akan mengembalikan error validasi. Namun, karena frontend tidak merender pesan error tersebut, modal form tetap terbuka dan tombol "Simpan" berhenti berputar tanpa pesan apa pun. User akan mengira aplikasi hang.
* **Rekomendasi:** Tambahkan elemen umpan balik visual di bawah setiap input:
  ```html
  <p v-if="form.errors.nama_dokumen" class="text-xs text-red-500 mt-1">
      {{ form.errors.nama_dokumen }}
  </p>
  ```

#### UX-02: Inkonsistensi Bahasa
* **Deskripsi:** Aplikasi e-SPMI menggunakan Bahasa Indonesia secara dominan. Namun, beberapa status empty state tabel di frontend masih tertulis dalam Bahasa Inggris (misal: `"No data available in table"` pada halaman dokumen).
* **Rekomendasi:** Ganti menjadi pesan lokal yang lebih bersahabat (misal: `"Tidak ada data dokumen tersedia"`).

---

### BAGIAN 6 — KONSISTENSI FITUR ANTAR ROLE

#### CONS-01: Kebocoran Data (Data Leakage) Skala Global pada Dashboard Auditee
* **Deskripsi:** Ini merupakan temuan dengan prioritas **Kritis**. Pada method `indexAuditee()` di `BerandaController.php` (yang merender dashboard saat user dengan role Auditee atau Unit Penunjang login), data statistik target nilai, skor evaluasi diri, dan nilai audit di-query secara global tanpa memfilter berdasarkan identitas pengguna:
  ```php
  // Target Nilai Mutu seluruh kampus (BUKAN milik Auditee pengakses saja!)
  $targetNilai = TargetNilaiMutu::when($periodeIds->isNotEmpty(), fn ($q) => $q->whereIn('pengaturan_periode_id', $periodeIds))
      ->avg('target_nilai');

  $nilaiEvaluasiDiri = EvaluasiDiri::when($periodeIds->isNotEmpty(), fn ($q) => $q->whereIn('pengaturan_periode_id', $periodeIds))
      ->avg('nilai_evaluasi');
  ```
* **Dampak:** Setiap program studi (Auditee) yang masuk ke dashboard akan melihat skor rata-rata keseluruhan sistem, bukan milik program studi mereka sendiri. Hal ini melanggar kerahasiaan data penilaian antar unit kerja.
* **Rekomendasi:** Batasi query dashboard auditee berdasarkan `auditee_id` or `unit_penunjang_id` milik user terautentikasi:
  ```php
  $targetNilai = TargetNilaiMutu::where('auditee_id', $user->auditee_id)->...
  ```

---

### RENCANA AKSI & IMPLEMENTASI PERBAIKAN

Berikut adalah petunjuk kode teknis (Sebelum & Sesudah) untuk memperbaiki temuan prioritas Kritis dan Tinggi:

#### 1. Perbaikan Kebocoran Data Dashboard Auditee (CONS-01)
* **File:** [BerandaController.php](file:///d:/KERJAAN/spmi-product/app/Http/Controllers/BerandaController.php) (Line 92-120)

##### Sebelum Perbaikan:
```php
$targetNilai = TargetNilaiMutu::when($periodeIds->isNotEmpty(), fn ($q) => $q->whereIn('pengaturan_periode_id', $periodeIds))
    ->avg('target_nilai');

$nilaiEvaluasiDiri = EvaluasiDiri::when($periodeIds->isNotEmpty(), fn ($q) => $q->whereIn('pengaturan_periode_id', $periodeIds))
    ->avg('nilai_evaluasi');

$nilaiHasilAudit = NilaiMutu::when($periodeIds->isNotEmpty(), fn ($q) => $q->whereHas('pengaturanPeriode', fn ($q2) => $q2->whereIn('id', $periodeIds)))
    ->when($filterLembaga, fn ($q) => $q->where('lembaga_akreditasi_id', $filterLembaga))
    ->avg('nilai');
```

##### Sesudah Perbaikan (Tersaring Berdasarkan Identitas User):
```php
$user = auth()->user();
$auditeeId = $user->auditee_id;
$unitId = $user->unit_penunjang_id;

$targetNilai = TargetNilaiMutu::when($periodeIds->isNotEmpty(), fn ($q) => $q->whereIn('pengaturan_periode_id', $periodeIds))
    ->when($auditeeId, fn ($q) => $q->where('auditee_id', $auditeeId))
    ->when($unitId, fn ($q) => $q->where('unit_penunjang_id', $unitId)) // jika unit penunjang memiliki target
    ->avg('target_nilai');

$nilaiEvaluasiDiri = EvaluasiDiri::when($periodeIds->isNotEmpty(), fn ($q) => $q->whereIn('pengaturan_periode_id', $periodeIds))
    ->when($auditeeId, fn ($q) => $q->where('auditee_id', $auditeeId))
    ->avg('nilai_evaluasi');

$nilaiHasilAudit = NilaiMutu::when($periodeIds->isNotEmpty(), fn ($q) => $q->whereHas('pengaturanPeriode', fn ($q2) => $q2->whereIn('id', $periodeIds)))
    ->when($filterLembaga, fn ($q) => $q->where('lembaga_akreditasi_id', $filterLembaga))
    ->when($auditeeId, fn ($q) => $q->where('auditee_id', $auditeeId))
    ->avg('nilai');
```

#### 2. Perbaikan Query N+1 Pengguna Backoffice (PERF-01)
* **File:** [PenggunaBackofficeController.php](file:///d:/KERJAAN/spmi-product/app/Http/Controllers/Pengaturan/PenggunaBackofficeController.php) (Line 20)

##### Sebelum Perbaikan:
```php
$query = User::role($roles);
```

##### Sesudah Perbaikan (Menggunakan Eager Loading):
```php
$query = User::with(['roles', 'auditeePusat'])->role($roles);
```

#### 3. Perbaikan Kerentanan Crash pada Eager Loading Null-Object (CODE-01)
* **File:** [DeskEvaluationAuditorController.php](file:///d:/KERJAAN/spmi-product/app/Http/Controllers/Auditor/DeskEvaluationAuditorController.php) (Line 28)

##### Sebelum Perbaikan:
```php
->map(fn ($group) => $group->map(fn ($e) => [
    'id'          => $e->auditee->id,
    'nama_auditee' => $e->auditee->nama_auditee,
])->values());
```

##### Sesudah Perbaikan (Menggunakan Null-Safe Operator):
```php
->map(fn ($group) => $group->map(fn ($e) => [
    'id'          => $e->auditee?->id,
    'nama_auditee' => $e->auditee?->nama_auditee ?? 'Auditee Terhapus/Tidak Diketahui',
])->values());
```

#### 4. Menambahkan Feedback Error Validasi pada Manajemen Dokumen Form (UX-01)
* **File:** [Index.vue (Manajemen Dokumen)](file:///d:/KERJAAN/spmi-product/resources/js/pages/Dokumen/ManajemenDokumen/Index.vue) (Line 369)

##### Contoh Penambahan Feedback Error di Bawah Input Nama Dokumen:
```html
<div class="grid grid-cols-[140px_1fr] items-center gap-x-4 gap-y-3 py-3 border-b">
    <label class="text-sm font-medium text-gray-600 text-right">Nama Dokumen</label>
    <div class="relative">
        <input v-model="form.nama_dokumen" type="text" class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none pr-6" />
        <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-red-500 text-sm">*</span>
    </div>
    <!-- Penambahan Error Validasi -->
    <div></div>
    <p v-if="form.errors.nama_dokumen" class="text-xs text-red-500 mt-1">
        {{ form.errors.nama_dokumen }}
    </p>
</div>
```

---
*Laporan audit ini dibuat dengan cermat untuk memastikan keandalan, keamanan, dan skalabilitas sistem e-SPMI. Segera jadwalkan proses perbaikan berdasarkan tingkat prioritas temuan.*
