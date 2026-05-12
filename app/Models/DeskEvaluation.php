<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeskEvaluation extends Model
{
    protected $table = 'desk_evaluation';

    protected $fillable = ['evaluasi_diri_id', 'auditor_id', 'indikator_id', 'nilai', 'catatan'];

    public function evaluasiDiri(): BelongsTo
    {
        return $this->belongsTo(EvaluasiDiri::class, 'evaluasi_diri_id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(Auditor::class, 'auditor_id');
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }

    public function temuan(): HasMany
    {
        return $this->hasMany(Temuan::class, 'desk_evaluation_id');
    }
}
