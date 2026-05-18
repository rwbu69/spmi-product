<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class SystemNotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $query = Activity::query();

        // Filter by Date Mulai / Date Akhir
        $query->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));

        // Filter by Causer User
        if ($request->user_id) {
            $query->where('causer_id', $request->user_id);
        }

        // Filter by Model Type
        if ($request->model_type) {
            $query->where('subject_type', $request->model_type);
        }

        // Filter by Action Event (created, updated, deleted)
        if ($request->event) {
            $query->where('event', $request->event);
        }

        $modelMapping = [
            'Auditee' => 'Auditee',
            'AuditeePusat' => 'Auditee Pusat',
            'Auditor' => 'Auditor',
            'DaftarKesesuaian' => 'Daftar Kesesuaian',
            'DaftarTemuanPp' => 'Daftar Temuan',
            'DeskEvaluation' => 'Desk Evaluation',
            'DraftLaporanRtm' => 'Draft RTM',
            'EvaluasiDiri' => 'Evaluasi Diri',
            'Indikator' => 'Indikator',
            'JenisDokumen' => 'Jenis Dokumen',
            'JenisTemuan' => 'Jenis Temuan',
            'KategoriDokumen' => 'Kategori Dokumen',
            'KategoriTemuan' => 'Kategori Temuan',
            'LaporanAmi' => 'Laporan AMI',
            'LembagaAkreditasi' => 'Lembaga Akreditasi',
            'ManajemenDokumen' => 'Manajemen Dokumen',
            'NilaiMutu' => 'Nilai Mutu',
            'PengaturanPeriode' => 'Pengaturan Periode',
            'RekapTemuanApproval' => 'Rekap Temuan Approval',
            'RencanaTindakLanjut' => 'Rencana Tindak Lanjut',
            'StandarMutu' => 'Standar Mutu',
            'TahunPeriode' => 'Tahun Periode',
            'TargetNilaiMutu' => 'Target Nilai Mutu',
            'Temuan' => 'Daftar Temuan',
            'UnitPenunjang' => 'Unit Penunjang',
            'UploadLaporanRtm' => 'Upload Laporan RTM',
            'User' => 'Pengguna',
        ];

        $perPage = (int) ($request->per_page ?? 10);
        $data = $query->with('causer')->latest()->paginate($perPage)->withQueryString();

        $data->getCollection()->transform(function ($activity) use ($modelMapping) {
            $causer = $activity->causer;
            $actorRoles = $causer && method_exists($causer, 'getRoleNames')
                ? $causer->getRoleNames()->implode(', ')
                : '-';

            $properties = $activity->properties ? $activity->properties->toArray() : [];
            
            // Read from data_lama and data_baru columns, falling back to properties JSON for older records
            $oldData = $activity->data_lama ? json_decode($activity->data_lama, true) : ($properties['old'] ?? null);
            $newData = $activity->data_baru ? json_decode($activity->data_baru, true) : ($properties['attributes'] ?? null);

            // Reconstruct model name beautifully with Indonesian mapping
            $subjectClass = $activity->subject_type;
            $subjectName = class_basename($subjectClass);
            $mappedName = $modelMapping[$subjectName] ?? $subjectName;

            // Format data sebelum and sesudah
            $oldFormatted = null;
            $newFormatted = null;

            if ($activity->event === 'created') {
                $oldFormatted = 'Tidak ada data sebelumnya';
                $newFormatted = $newData ? self::formatAttributes($newData) : '-';
            } elseif ($activity->event === 'updated') {
                $oldFormatted = $oldData ? self::formatAttributes($oldData) : '-';
                $newFormatted = $newData ? self::formatAttributes($newData) : '-';
            } elseif ($activity->event === 'deleted') {
                $oldFormatted = $oldData ? self::formatAttributes($oldData) : '-';
                $newFormatted = 'Data telah dihapus';
            } else {
                $oldFormatted = $oldData ? self::formatAttributes($oldData) : '-';
                $newFormatted = $newData ? self::formatAttributes($newData) : '-';
            }

            return [
                'id' => $activity->id,
                'event' => $activity->event ?? $activity->description,
                'subject_type' => $mappedName ?: 'Sistem',
                'subject_class' => $subjectClass,
                'causer_name' => $causer?->name ?? 'Sistem',
                'causer_role' => $actorRoles ?: '-',
                'time' => $activity->created_at?->translatedFormat('d M Y H:i:s') ?? '',
                'time_human' => $activity->created_at?->diffForHumans() ?? '',
                'old_data' => $oldFormatted,
                'new_data' => $newFormatted,
            ];
        });

        // Unique model list that exists in the activity logs
        $modelOptions = Activity::groupBy('subject_type')
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(fn ($type) => [
                'value' => $type,
                'label' => $modelMapping[class_basename($type)] ?? class_basename($type),
            ]);

        // Users who performed actions
        $userOptions = User::orderBy('name')->get(['id', 'name']);

        // Unique list of events/actions
        $actionOptions = ['created', 'updated', 'deleted'];

        return Inertia::render('Admin/LogSistem/Index', [
            'data' => $data,
            'filters' => $request->only(['date_from', 'date_to', 'user_id', 'model_type', 'event', 'per_page']),
            'modelOptions' => $modelOptions,
            'userOptions' => $userOptions,
            'actionOptions' => $actionOptions,
        ]);
    }

    private static function formatAttributes(array $attributes): array
    {
        $fieldMapping = [
            'name' => 'Nama',
            'username' => 'Username',
            'password' => 'Kata Sandi',
            'keterangan' => 'Keterangan',
            'is_active' => 'Status Aktif',
            'nama' => 'Nama',
            'keahlian' => 'Keahlian',
            'no_hp' => 'No. HP',
            'tahun' => 'Tahun',
            'status' => 'Status',
            'periode' => 'Periode',
            'nama_kategori' => 'Nama Kategori',
            'jenis_temuan_id' => 'ID Jenis Temuan',
            'nilai' => 'Nilai',
            'catatan' => 'Catatan',
            'deskripsi' => 'Deskripsi',
            'rekomendasi' => 'Rekomendasi',
            'status_approval' => 'Status Approval',
            'file_laporan' => 'File Laporan',
            'tanggal_laporan' => 'Tanggal Laporan',
            'deleted_at' => 'Waktu Dihapus',
            'created_at' => 'Waktu Dibuat',
            'updated_at' => 'Waktu Diperbarui',
            'nama_auditee' => 'Nama Auditee',
            'nama_unit' => 'Nama Unit Penunjang',
            'nama_lembaga' => 'Nama Lembaga Akreditasi',
            'nama_jenis' => 'Nama Jenis Dokumen',
            'nama_standar' => 'Nama Standar Mutu',
            'kode' => 'Kode',
            'kategori_temuan_id' => 'Kategori Temuan',
            'auditee_id' => 'ID Auditee',
            'auditor_id' => 'ID Auditor',
            'target' => 'Target',
            'bobot' => 'Bobot',
            'skor' => 'Skor',
            'nilai_mutu' => 'Nilai Mutu',
            'tahun_periode_id' => 'ID Tahun Periode',
            'draft_laporan_rtm_id' => 'ID Draft Laporan RTM',
            'upload_laporan_rtm_id' => 'ID Upload Laporan RTM',
        ];

        $formatted = [];
        foreach ($attributes as $key => $value) {
            // Skip showing hashed passwords
            if ($key === 'password') {
                $value = '[DISEMBUNYIKAN]';
            }
            if ($key === 'is_active') {
                $value = $value ? 'Aktif' : 'Tidak Aktif';
            }
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }

            $label = $fieldMapping[$key] ?? ucwords(str_replace('_', ' ', $key));
            $formatted[] = [
                'label' => $label,
                'value' => $value ?? '-',
            ];
        }

        return $formatted;
    }
}
