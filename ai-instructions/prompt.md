You are an expert Full-Stack Developer specializing in the VILT Stack (Vue 3, Inertia.js, Laravel, Tailwind CSS). Your task is to build a web-based eSPMI (Sistem Penjaminan Mutu Internal) application. I have attached the ERD (eSPMI.pdf) and will provide the detailed requirements for the frontend menus and backend business logic.

Tech Stack Constraints & Best Practices:

    Backend: Laravel (latest version). Use Eloquent Models with strict relationships (belongsTo, hasMany, etc.) based on the ERD. Use Form Requests for all backend validations.

    Routing & Controller: Do NOT build a separate REST API in api.php. Use web.php and return Inertia responses using Inertia::render('ViewPath', [data]).

    Frontend: Vue 3 using the Composition API (<script setup>). Use Tailwind CSS for all styling.

    Data Fetching & Forms: Use Inertia's Link component for navigation to avoid full page reloads. Use Inertia's useForm helper for all data submissions and file uploads to easily handle progress states and backend validation errors.

Task 1: Database & Schema Generation

    Analyze the attached ERD (eSPMI.pdf).

    Generate the Laravel Migrations and Eloquent Models. Ensure all foreign keys, indexes, and relationships are properly defined in the models.

Task 2: Global System Requirements

    Data Tables: All menus displaying data must have a search bar and pagination. Implement this efficiently using Laravel's paginate() and Inertia's router for partial reloads (passing search queries as URL parameters).

    File Uploads: Implement validation for file types (PDF only) and sizes (e.g., mimes:pdf|max:2048 in Laravel Form Request). Store files securely using Laravel's storage facade.

    Flash Messages: Use Inertia shared data to pass success/error flash messages from Laravel sessions to Vue.

Task 3: Implement Menus and Business Logic
Please build the Controllers, Web Routes, and Vue Pages for the following menu structure:

    Beranda (Dashboard):

        Display counts for: Auditee, Lembaga Akreditasi, and Standar Mutu.

        Implement dropdown filters: Lembaga Akreditasi, Tahun, Auditee, and Jenis (triggering Inertia partial reloads).

        Display a chart showing "Persentase Rata Nilai" (You can use a lightweight Vue charting library like Chart.js/vue-chartjs).

    Manajemen Referensi:

        Tahun Periode: Table with No, Tahun, status (aktif/tidak aktif). Constraint: Active periods cannot be deleted (validate in Controller).

        Lembaga Akreditasi: CRUD for Nama Lembaga and Keterangan.

        Auditee Pusat: Standard CRUD functionality.

        Auditee: CRUD table showing Kode, Nama, Jenjang, Auditee Pusat, and Alamat. Form includes dropdowns (Pusat, Jenjang, Akreditasi A/B/C/Baik/Baik Sekali/Unggul), SK info, and a PDF file upload (max 2MB).

        Unit Penunjang: Standard CRUD for Kode, Nama, Jenjang (dropdown), Alamat, and Keterangan.

    Manajemen Dokumen:

        Kategori & Jenis Dokumen: Standard CRUD. Jenis Dokumen links to Kategori via dropdown.

        Manajemen Dokumen: Table with sortable columns. Needs filters for Kategori, Unit Pengunggah, and User Pengunggah. Upload form requires PDF (max 7MB).

    Penetapan:

        Daftar Nilai Mutu: CRUD table with Dropdown filters (Tahun, Lembaga).

        Daftar Standar Mutu: Complex hierarchical table with Expand/Collapse All. Form requires a WYSIWYG editor (e.g., Quill.js or Trix) for Nama Standar, Data Dukung, and Deskripsi. Must support Parent-Child relationships.

    Pelaksanaan:

        Pengaturan Periode: CRUD with Date Range Pickers. Strict Constraint: Periods with active status cannot be deleted, and deletion is blocked if relational data exists in Evaluasi Diri, Desk Evaluasi, Visitasi, Standar Mutu, Target Nilai, or Nilai Mutu (Catch SQL constraint violations or check exists() in Laravel).

        Target Nilai Mutu: CRUD table with dropdown filters and sortable columns.

        Evaluasi Diri: Read-only table with sortable columns and dropdown filters.

    Evaluasi AMI:

        Manajemen Auditor: CRUD with detailed view modal.

        Jenis Temuan: CRUD with Radio button status (Positif/Negatif).

        Kategori Temuan: CRUD linked to Jenis Temuan (Kesesuaian/KTS/Observasi).

        Daftar Temuan Kolektif & Rekap Desk Evaluation: Read-only tables with filters. Include an Excel Export feature (using Laravel Excel / Maatwebsite).

        Laporan AMI: Read-only table filtered by Auditee.

    Pengendalian & Peningkatan:

        Daftar Temuan & Daftar Kesesuaian: Read-only tables with filters.

        Draft Laporan RTM: Read-only table with a Download file button.

        Upload Laporan RTM: Upload form for PDF files (max 7MB) and sortable data table.

    Pengaturan Sistem:

        Pengguna Portal: Standard CRUD with password hash update capability.

        Pengguna Backoffice: Standard CRUD with dropdowns for unit kerja, satuan kerja, and group.

Please structure the project systematically. Start by creating the Migrations and Models. Then, proceed menu by menu, creating the Route, Controller, Form Request, and finally the Vue (.vue) page component.

Take it step by step. 
