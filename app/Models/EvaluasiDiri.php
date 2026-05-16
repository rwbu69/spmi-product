<?php

namespace App\Models;

use App\Models\Concerns\LogsMenuActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluasiDiri extends Model
{
    use LogsMenuActivity;

    protected $table = 'evaluasi_diri';

    protected $fillable = ['auditee_id', 'pengaturan_periode_id', 'nilai_evaluasi', 'status'];

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }

    public function pengaturanPeriode(): BelongsTo
    {
        return $this->belongsTo(PengaturanPeriode::class, 'pengaturan_periode_id');
    }

    public function deskEvaluasi(): HasMany
    {
        return $this->hasMany(DeskEvaluation::class, 'evaluasi_diri_id');
    }
}
