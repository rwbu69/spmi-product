<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RencanaTindakLanjut extends Model
{
    protected $table = 'rencana_tindak_lanjut';

    protected $fillable = [
        'daftar_temuan_pp_id',
        'auditee_id',
        'uraian_rtm',
        'penanggung_jawab',
        'target_selesai',
        'status',
    ];

    protected $casts = [
        'target_selesai' => 'date',
    ];

    public function daftarTemuanPp(): BelongsTo
    {
        return $this->belongsTo(DaftarTemuanPp::class, 'daftar_temuan_pp_id');
    }

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }

    public function draftLaporanRtm(): HasMany
    {
        return $this->hasMany(DraftLaporanRtm::class, 'rencana_tindak_lanjut_id');
    }
}
