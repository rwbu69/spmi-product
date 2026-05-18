<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\LogsMenuActivity;

class ManajemenDokumen extends Model
{
    use SoftDeletes, LogsMenuActivity;

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query;
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
