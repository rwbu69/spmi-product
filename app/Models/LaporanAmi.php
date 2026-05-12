<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanAmi extends Model
{
    protected $table = 'laporan_ami';

    protected $fillable = [
        'pengaturan_periode_id',
        'auditee_id',
        'file_laporan',
        'tanggal_laporan',
        'status',
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
    ];

    public function pengaturanPeriode(): BelongsTo
    {
        return $this->belongsTo(PengaturanPeriode::class, 'pengaturan_periode_id');
    }

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }
}
