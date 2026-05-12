<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditee extends Model
{
    protected $table = 'auditee';

    protected $fillable = [
        'kode',
        'nama_auditee',
        'jenjang',
        'auditee_pusat_id',
        'alamat',
        'akreditasi',
        'sk_no',
        'sk_tanggal',
        'sk_file_path',
    ];

    protected $casts = [
        'sk_tanggal' => 'date',
    ];

    public function auditeePusat(): BelongsTo
    {
        return $this->belongsTo(AuditeePusat::class, 'auditee_pusat_id');
    }

    public function evaluasiDiri(): HasMany
    {
        return $this->hasMany(EvaluasiDiri::class, 'auditee_id');
    }

    public function targetNilaiMutu(): HasMany
    {
        return $this->hasMany(TargetNilaiMutu::class, 'auditee_id');
    }

    public function manajemenDokumen(): HasMany
    {
        return $this->hasMany(ManajemenDokumen::class, 'auditee_id');
    }

    public function daftarTemuanPp(): HasMany
    {
        return $this->hasMany(DaftarTemuanPp::class, 'auditee_id');
    }

    public function daftarKesesuaian(): HasMany
    {
        return $this->hasMany(DaftarKesesuaian::class, 'auditee_id');
    }

    public function laporanAmi(): HasMany
    {
        return $this->hasMany(LaporanAmi::class, 'auditee_id');
    }

    public function rekapTemuanApproval(): HasMany
    {
        return $this->hasMany(RekapTemuanApproval::class, 'auditee_id');
    }

    public function nilaiMutu(): HasMany
    {
        return $this->hasMany(NilaiMutu::class, 'auditee_id');
    }
}
