<?php

namespace App\Models;

use App\Models\Concerns\LogsMenuActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DraftLaporanRtm extends Model
{
    use LogsMenuActivity;

    protected $table = 'draft_laporan_rtm';

    protected $fillable = [
        'rencana_tindak_lanjut_id',
        'pengaturan_periode_id',
        'auditee_id',
        'nama_dokumen',
        'file_path',
        'tanggal_dibuat',
        'status',
    ];

    protected $casts = [
        'tanggal_dibuat' => 'date',
    ];

    public function rencanaTindakLanjut(): BelongsTo
    {
        return $this->belongsTo(RencanaTindakLanjut::class, 'rencana_tindak_lanjut_id');
    }

    public function pengaturanPeriode(): BelongsTo
    {
        return $this->belongsTo(PengaturanPeriode::class, 'pengaturan_periode_id');
    }

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }

    public function uploadLaporanRtm(): HasMany
    {
        return $this->hasMany(UploadLaporanRtm::class, 'draft_laporan_rtm_id');
    }
}
