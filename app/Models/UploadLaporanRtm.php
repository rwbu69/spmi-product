<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadLaporanRtm extends Model
{
    protected $table = 'upload_laporan_rtm';

    protected $fillable = [
        'draft_laporan_rtm_id',
        'auditee_id',
        'tahun_periode_id',
        'nama_dokumen',
        'file_path',
        'tanggal_upload',
        'status_download',
    ];

    protected $casts = [
        'tanggal_upload' => 'datetime',
    ];

    public function draftLaporanRtm(): BelongsTo
    {
        return $this->belongsTo(DraftLaporanRtm::class, 'draft_laporan_rtm_id');
    }

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }

    public function tahunPeriode(): BelongsTo
    {
        return $this->belongsTo(TahunPeriode::class, 'tahun_periode_id');
    }
}
