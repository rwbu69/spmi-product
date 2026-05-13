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
                $activities = [];
                
                // Track Auditee
                $auditee = \App\Models\Auditee::latest('updated_at')->first();
                if ($auditee) {
                    $activities[] = [
                        'title' => 'Data Auditee',
                        'message' => "Auditee '{$auditee->nama_auditee}' " . ($auditee->created_at == $auditee->updated_at ? 'ditambahkan' : 'diperbarui'),
                        'updated_at' => $auditee->updated_at,
                        'time' => $auditee->updated_at->diffForHumans(),
                        'type' => 'blue',
                        'link' => route('referensi.auditee.index'),
                    ];
                }

                // Track Standar Mutu
                $standar = \App\Models\StandarMutu::latest('updated_at')->first();
                if ($standar) {
                    $activities[] = [
                        'title' => 'Standar Mutu',
                        'message' => "Standar Mutu '{$standar->nama_standar}' " . ($standar->created_at == $standar->updated_at ? 'ditambahkan' : 'diperbarui'),
                        'updated_at' => $standar->updated_at,
                        'time' => $standar->updated_at->diffForHumans(),
                        'type' => 'amber',
                        'link' => route('penetapan.standar-mutu.index'),
                    ];
                }

                // Track Evaluasi Diri
                $evaluasi = \App\Models\EvaluasiDiri::with('auditee')->latest('updated_at')->first();
                if ($evaluasi) {
                    $activities[] = [
                        'title' => 'Evaluasi Diri',
                        'message' => "Evaluasi diri {$evaluasi->auditee->nama_auditee} telah diperbarui dengan skor {$evaluasi->nilai_evaluasi}.",
                        'updated_at' => $evaluasi->updated_at,
                        'time' => $evaluasi->updated_at->diffForHumans(),
                        'type' => 'green',
                        'link' => route('pelaksanaan.evaluasi-diri.index'),
                    ];
                }

                // Track Nilai Mutu
                $nilai = \App\Models\NilaiMutu::with('auditee')->latest('updated_at')->first();
                if ($nilai) {
                    $activities[] = [
                        'title' => 'Nilai Mutu',
                        'message' => "Nilai mutu akhir untuk {$nilai->auditee->nama_auditee} telah diperbarui menjadi {$nilai->nilai}.",
                        'updated_at' => $nilai->updated_at,
                        'time' => $nilai->updated_at->diffForHumans(),
                        'type' => 'green',
                        'link' => route('penetapan.nilai-mutu.index'),
                    ];
                }

                // Track Auditor
                $auditor = \App\Models\Auditor::latest('updated_at')->first();
                if ($auditor) {
                    $activities[] = [
                        'title' => 'Tim Auditor',
                        'message' => "Data Auditor '{$auditor->nama}' " . ($auditor->created_at == $auditor->updated_at ? 'ditambahkan' : 'diperbarui'),
                        'updated_at' => $auditor->updated_at,
                        'time' => $auditor->updated_at->diffForHumans(),
                        'type' => 'blue',
                        'link' => route('ami.auditor.index'),
                    ];
                }

                // Track Unit Penunjang
                $unit = \App\Models\UnitPenunjang::latest('updated_at')->first();
                if ($unit) {
                    $activities[] = [
                        'title' => 'Unit Penunjang',
                        'message' => "Unit '{$unit->nama_unit}' " . ($unit->created_at == $unit->updated_at ? 'ditambahkan' : 'diperbarui'),
                        'updated_at' => $unit->updated_at,
                        'time' => $unit->updated_at->diffForHumans(),
                        'type' => 'amber',
                        'link' => route('referensi.unit-penunjang.index'),
                    ];
                }

                // Track Expiring Auditees (Assuming 5 year validity, notify 1 year before)
                $expiringAuditees = \App\Models\Auditee::whereNotNull('sk_tanggal')
                    ->whereRaw('DATE_ADD(sk_tanggal, INTERVAL 4 YEAR) <= ?', [now()])
                    ->whereRaw('DATE_ADD(sk_tanggal, INTERVAL 5 YEAR) >= ?', [now()])
                    ->count();

                if ($expiringAuditees > 0) {
                    $activities[] = [
                        'title' => 'Pemberitahuan Auditee Expired',
                        'message' => "Ada Sebanyak {$expiringAuditees} Auditee yang akan Expired Tahun Depan",
                        'updated_at' => now(),
                        'time' => now()->diffForHumans(),
                        'type' => 'red',
                        'link' => route('referensi.auditee.index'),
                    ];
                }

                // Sort and get top 5
                usort($activities, fn($a, $b) => $b['updated_at'] <=> $a['updated_at']);
                $notifications = array_slice($activities, 0, 5);

            } catch (\Exception $e) {
                // Silently ignore
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user'  => $request->user(),
                'roles' => $request->user() ? $request->user()->getRoleNames()->toArray() : [],
                'role'  => $request->user() ? ($request->user()->getRoleNames()->first() ?? '') : '',
            ],
            'notifications' => $notifications,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
        ];
    }
}
