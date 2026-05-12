<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManajemenDokumen extends Model
{
    protected $table = 'manajemen_dokumen';

    protected $fillable = [
        'jenis_dokumen_id',
        'auditee_id',
        'user_id',
        'nama_dokumen',
        'tahun',
        'file_path',
    ];

    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(JenisDokumen::class, 'jenis_dokumen_id');
    }

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
