<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\LogsMenuActivity;

class LaporanAmi extends Model
{
    use LogsMenuActivity;

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

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query;
    }


    public function pengaturanPeriode(): BelongsTo
    {
        return $this->belongsTo(PengaturanPeriode::class, 'pengaturan_periode_id');
    }

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }
}
