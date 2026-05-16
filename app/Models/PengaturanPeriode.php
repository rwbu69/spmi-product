<?php

namespace App\Models;

use App\Models\Concerns\LogsMenuActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengaturanPeriode extends Model
{
    use LogsMenuActivity;

    protected $table = 'pengaturan_periode';

    protected $fillable = [
        'tahun_periode_id',
        'lembaga_akreditasi_id',
        'mulai_evaluasi_diri',
        'akhir_evaluasi_diri',
        'mulai_desk_eval',
        'akhir_desk_eval',
        'mulai_visitasi',
        'akhir_visitasi',
        'status',
    ];

    protected $casts = [
        'mulai_evaluasi_diri' => 'date',
        'akhir_evaluasi_diri' => 'date',
        'mulai_desk_eval'     => 'date',
        'akhir_desk_eval'     => 'date',
        'mulai_visitasi'      => 'date',
        'akhir_visitasi'      => 'date',
    ];

    public function tahunPeriode(): BelongsTo
    {
        return $this->belongsTo(TahunPeriode::class, 'tahun_periode_id');
    }

    public function lembagaAkreditasi(): BelongsTo
    {
        return $this->belongsTo(LembagaAkreditasi::class, 'lembaga_akreditasi_id');
    }

    public function targetNilaiMutu(): HasMany
    {
        return $this->hasMany(TargetNilaiMutu::class, 'pengaturan_periode_id');
    }

    public function evaluasiDiri(): HasMany
    {
        return $this->hasMany(EvaluasiDiri::class, 'pengaturan_periode_id');
    }

    public function laporanAmi(): HasMany
    {
        return $this->hasMany(LaporanAmi::class, 'pengaturan_periode_id');
    }

    public function rekapTemuanApproval(): HasMany
    {
        return $this->hasMany(RekapTemuanApproval::class, 'pengaturan_periode_id');
    }

    public function daftarTemuanPp(): HasMany
    {
        return $this->hasMany(DaftarTemuanPp::class, 'pengaturan_periode_id');
    }

    public function daftarKesesuaian(): HasMany
    {
        return $this->hasMany(DaftarKesesuaian::class, 'pengaturan_periode_id');
    }

    public function nilaiMutu(): HasMany
    {
        return $this->hasMany(NilaiMutu::class, 'pengaturan_periode_id');
    }
}
