<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DaftarTemuanPp extends Model
{
    protected $table = 'daftar_temuan_pp';

    protected $fillable = [
        'temuan_id',
        'rekap_approval_id',
        'auditee_id',
        'pengaturan_periode_id',
        'uraian_temuan',
        'jenis',
        'status',
    ];

    public function temuan(): BelongsTo
    {
        return $this->belongsTo(Temuan::class, 'temuan_id');
    }

    public function rekapApproval(): BelongsTo
    {
        return $this->belongsTo(RekapTemuanApproval::class, 'rekap_approval_id');
    }

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }

    public function pengaturanPeriode(): BelongsTo
    {
        return $this->belongsTo(PengaturanPeriode::class, 'pengaturan_periode_id');
    }

    public function rencanaTindakLanjut(): HasMany
    {
        return $this->hasMany(RencanaTindakLanjut::class, 'daftar_temuan_pp_id');
    }
}
