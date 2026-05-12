<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DaftarKesesuaian extends Model
{
    protected $table = 'daftar_kesesuaian';

    protected $fillable = [
        'auditee_id',
        'standar_mutu_id',
        'pengaturan_periode_id',
        'deskripsi',
        'peningkatan',
        'nilai_mutu',
        'temuan_positif',
        'filter_tampilan',
    ];

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }

    public function standarMutu(): BelongsTo
    {
        return $this->belongsTo(StandarMutu::class, 'standar_mutu_id');
    }

    public function pengaturanPeriode(): BelongsTo
    {
        return $this->belongsTo(PengaturanPeriode::class, 'pengaturan_periode_id');
    }
}
