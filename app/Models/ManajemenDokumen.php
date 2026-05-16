<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ManajemenDokumen extends Model
{
    use SoftDeletes, LogsActivity;

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['Admin', 'Fakultas'])) {
            return $query;
        }

        if (! $user->hasAnyRole(['Auditee', 'Unit Penunjang'])) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $builder) use ($user) {
            $builder->where('user_id', $user->id)
                ->orWhere(function (Builder $sub) {
                    $sub->whereNull('auditee_id')->whereNull('unit_penunjang_id');
                });

            if ($user->auditee_id) {
                $builder->orWhere('auditee_id', $user->auditee_id);
            }

            if ($user->unit_penunjang_id) {
                $builder->orWhere('unit_penunjang_id', $user->unit_penunjang_id);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $table = 'manajemen_dokumen';

    protected $fillable = [
        'jenis_dokumen_id',
        'auditee_id',
        'unit_penunjang_id',
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

    public function unitPenunjang(): BelongsTo
    {
        return $this->belongsTo(UnitPenunjang::class, 'unit_penunjang_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
