<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indikator extends Model
{
    protected $table = 'indikator';

    protected $fillable = ['standar_mutu_id', 'deskripsi', 'bobot'];

    public function standarMutu(): BelongsTo
    {
        return $this->belongsTo(StandarMutu::class, 'standar_mutu_id');
    }

    public function deskEvaluasi(): HasMany
    {
        return $this->hasMany(DeskEvaluation::class, 'indikator_id');
    }
}
