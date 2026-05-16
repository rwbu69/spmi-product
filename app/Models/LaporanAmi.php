<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class LaporanAmi extends Model
{
    use LogsActivity;

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
        if ($user->hasAnyRole(['Admin', 'Auditor', 'Fakultas'])) {
            return $query;
        }

        if ($user->auditee_id) {
            return $query->where('auditee_id', $user->auditee_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
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
