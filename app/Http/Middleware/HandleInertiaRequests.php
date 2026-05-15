<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $notifications = [];
        if ($request->user()) {
            try {
                $user  = $request->user();
                $roles = $user->getRoleNames()->toArray();

                $isAuditor = in_array('Auditor', $roles);
                $isAuditee = in_array('Auditee', $roles) || in_array('Unit Penunjang', $roles);
                $isAdmin   = ! $isAuditor && ! $isAuditee; // Admin, Fakultas, etc.

                $activities = [];

                // ── AUDITOR notifications ──────────────────────────────────────────────
                // Only: evaluasi diri list, desk evaluasi, laporan AMI, upload laporan
                if ($isAuditor) {
                    $evaluasi = \App\Models\EvaluasiDiri::with('auditee')->latest('updated_at')->first();
                    if ($evaluasi) {
                        $activities[] = [
                            'title'      => 'Evaluasi Diri Auditee',
                            'message'    => "Evaluasi diri {$evaluasi->auditee->nama_auditee} diperbarui (skor {$evaluasi->nilai_evaluasi}).",
                            'updated_at' => $evaluasi->updated_at,
                            'time'       => $evaluasi->updated_at->diffForHumans(),
                            'type'       => 'green',
                            'link'       => route('pelaksanaan.evaluasi-diri.index'),
                        ];
                    }

                    $laporan = \App\Models\LaporanAmi::with('auditee')->latest('updated_at')->first();
                    if ($laporan) {
                        $activities[] = [
                            'title'      => 'Laporan AMI',
                            'message'    => "Laporan AMI untuk {$laporan->auditee->nama_auditee} telah diperbarui.",
                            'updated_at' => $laporan->updated_at,
                            'time'       => $laporan->updated_at->diffForHumans(),
                            'type'       => 'blue',
                            'link'       => route('auditor.upload-laporan-ami.index'),
                        ];
                    }

                    $standar = \App\Models\StandarMutu::latest('updated_at')->first();
                    if ($standar) {
                        $activities[] = [
                            'title'      => 'Standar Mutu',
                            'message'    => "Standar '{$standar->nama_standar}' baru tersedia untuk desk evaluasi.",
                            'updated_at' => $standar->updated_at,
                            'time'       => $standar->updated_at->diffForHumans(),
                            'type'       => 'amber',
                            'link'       => route('auditor.upload-laporan-ami.index'),
                        ];
                    }
                }

                // ── AUDITEE / UNIT PENUNJANG notifications ─────────────────────────────
                // Only: their own evaluasi diri status, dokumen uploaded to them, laporan AMI mereka
                elseif ($isAuditee) {
                    $evaluasi = \App\Models\EvaluasiDiri::with('auditee')->latest('updated_at')->first();
                    if ($evaluasi) {
                        $activities[] = [
                            'title'      => 'Status Evaluasi Diri',
                            'message'    => "Status evaluasi diri Anda: {$evaluasi->status}.",
                            'updated_at' => $evaluasi->updated_at,
                            'time'       => $evaluasi->updated_at->diffForHumans(),
                            'type'       => 'green',
                            'link'       => '/pelaksanaan/evaluasi-diri',
                        ];
                    }

                    $dokumen = \App\Models\ManajemenDokumen::latest('updated_at')->first();
                    if ($dokumen) {
                        $activities[] = [
                            'title'      => 'Dokumen Baru',
                            'message'    => "Dokumen '{$dokumen->nama_dokumen}' telah diunggah ke sistem.",
                            'updated_at' => $dokumen->updated_at,
                            'time'       => $dokumen->updated_at->diffForHumans(),
                            'type'       => 'blue',
                            'link'       => '/dokumen/manajemen',
                        ];
                    }

                    $laporan = \App\Models\LaporanAmi::latest('updated_at')->first();
                    if ($laporan) {
                        $activities[] = [
                            'title'      => 'Laporan AMI',
                            'message'    => "Laporan AMI tersedia untuk diunduh.",
                            'updated_at' => $laporan->updated_at,
                            'time'       => $laporan->updated_at->diffForHumans(),
                            'type'       => 'amber',
                            'link'       => '/ami/laporan-ami',
                        ];
                    }
                }

                // ── ADMIN / FAKULTAS notifications ────────────────────────────────────
                // Full system activity + expiring auditee warning
                else {
                    $auditee = \App\Models\Auditee::latest('updated_at')->first();
                    if ($auditee) {
                        $activities[] = [
                            'title'      => 'Data Auditee',
                            'message'    => "Auditee '{$auditee->nama_auditee}' " . ($auditee->created_at == $auditee->updated_at ? 'ditambahkan' : 'diperbarui'),
                            'updated_at' => $auditee->updated_at,
                            'time'       => $auditee->updated_at->diffForHumans(),
                            'type'       => 'blue',
                            'link'       => route('referensi.auditee.index'),
                        ];
                    }

                    $standar = \App\Models\StandarMutu::latest('updated_at')->first();
                    if ($standar) {
                        $activities[] = [
                            'title'      => 'Standar Mutu',
                            'message'    => "Standar Mutu '{$standar->nama_standar}' " . ($standar->created_at == $standar->updated_at ? 'ditambahkan' : 'diperbarui'),
                            'updated_at' => $standar->updated_at,
                            'time'       => $standar->updated_at->diffForHumans(),
                            'type'       => 'amber',
                            'link'       => route('penetapan.standar-mutu.index'),
                        ];
                    }

                    $evaluasi = \App\Models\EvaluasiDiri::with('auditee')->latest('updated_at')->first();
                    if ($evaluasi) {
                        $activities[] = [
                            'title'      => 'Evaluasi Diri',
                            'message'    => "Evaluasi diri {$evaluasi->auditee->nama_auditee} diperbarui (skor {$evaluasi->nilai_evaluasi}).",
                            'updated_at' => $evaluasi->updated_at,
                            'time'       => $evaluasi->updated_at->diffForHumans(),
                            'type'       => 'green',
                            'link'       => route('pelaksanaan.evaluasi-diri.index'),
                        ];
                    }

                    $nilai = \App\Models\NilaiMutu::with('auditee')->latest('updated_at')->first();
                    if ($nilai) {
                        $activities[] = [
                            'title'      => 'Nilai Mutu',
                            'message'    => "Nilai mutu {$nilai->auditee->nama_auditee} diperbarui menjadi {$nilai->nilai}.",
                            'updated_at' => $nilai->updated_at,
                            'time'       => $nilai->updated_at->diffForHumans(),
                            'type'       => 'green',
                            'link'       => route('penetapan.nilai-mutu.index'),
                        ];
                    }

                    $auditorModel = \App\Models\Auditor::latest('updated_at')->first();
                    if ($auditorModel) {
                        $activities[] = [
                            'title'      => 'Tim Auditor',
                            'message'    => "Data Auditor '{$auditorModel->nama}' " . ($auditorModel->created_at == $auditorModel->updated_at ? 'ditambahkan' : 'diperbarui'),
                            'updated_at' => $auditorModel->updated_at,
                            'time'       => $auditorModel->updated_at->diffForHumans(),
                            'type'       => 'blue',
                            'link'       => route('ami.auditor.index'),
                        ];
                    }

                    $unit = \App\Models\UnitPenunjang::latest('updated_at')->first();
                    if ($unit) {
                        $activities[] = [
                            'title'      => 'Unit Penunjang',
                            'message'    => "Unit '{$unit->nama_unit}' " . ($unit->created_at == $unit->updated_at ? 'ditambahkan' : 'diperbarui'),
                            'updated_at' => $unit->updated_at,
                            'time'       => $unit->updated_at->diffForHumans(),
                            'type'       => 'amber',
                            'link'       => route('referensi.unit-penunjang.index'),
                        ];
                    }

                    // Expiring auditee warning — Admin/Fakultas/other only (not Auditor/Auditee)
                    // One notification per expiring auditee so they each appear individually.
                    // Uses sk_tanggal_selesai (accreditation end date) for precise expiry tracking.
                    $expiringAuditees = \App\Models\Auditee::whereNotNull('sk_tanggal_selesai')
                        ->where('sk_tanggal_selesai', '<=', now()->addYear())
                        ->get(['id', 'nama_auditee', 'jenjang', 'sk_tanggal_selesai']);

                    foreach ($expiringAuditees as $i => $ea) {
                        // Increment by $i seconds to keep them ordered and above normal activities
                        $expiredAt = now()->addSeconds($i + 1);
                        $expiredDate = \Carbon\Carbon::parse($ea->sk_tanggal_selesai);
                        $isExpired = $expiredDate->isPast();

                        $activities[] = [
                            'title'      => 'Pemberitahuan Auditee Expired',
                            'message'    => $isExpired
                                ? "{$ea->nama_auditee} telah Expired pada {$expiredDate->format('d-m-Y')}"
                                : "{$ea->nama_auditee} akan Expired pada {$expiredDate->format('d-m-Y')}",
                            'updated_at' => $expiredAt,
                            'time'       => $expiredDate->format('d-m-Y'),
                            'type'       => 'red',
                            'link'       => route('referensi.auditee.index'),
                        ];
                    }
                }

                // Sort descending by updated_at — latest (or highest timestamp) at the top
                usort($activities, fn ($a, $b) => $b['updated_at'] <=> $a['updated_at']);
                $notifications = array_slice($activities, 0, 5);

            } catch (\Exception $e) {
                // Silently ignore
            }
        }

        // Resolve active period year
        $periodeAktif = null;
        try {
            $aktif = \App\Models\TahunPeriode::where('status', 'Aktif')->first();
            $periodeAktif = $aktif?->tahun;
        } catch (\Exception $e) {
            // Silently ignore
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user'  => $request->user(),
                'roles' => $request->user() ? $request->user()->getRoleNames()->toArray() : [],
                'role'  => $request->user() ? ($request->user()->getRoleNames()->first() ?? '') : '',
            ],
            'periodeAktif'  => $periodeAktif,
            'notifications' => $notifications,
            'sidebarOpen'   => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
        ];
    }
}
