<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapTemuanApproval extends Model
{
    protected $table = 'rekap_temuan_approval';

    protected $fillable = [
        'auditee_id',
        'pengaturan_periode_id',
        'jumlah_temuan',
        'status_approval',
        'tanggal_approval',
        'approved_by',
    ];

    protected $casts = [
        'tanggal_approval' => 'date',
    ];

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }

    public function pengaturanPeriode(): BelongsTo
    {
        return $this->belongsTo(PengaturanPeriode::class, 'pengaturan_periode_id');
    }
}
