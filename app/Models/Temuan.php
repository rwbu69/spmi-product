<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Temuan extends Model
{
    protected $table = 'temuan';

    protected $fillable = [
        'desk_evaluation_id',
        'kategori_temuan_id',
        'deskripsi',
        'rekomendasi',
        'status_approval',
    ];

    public function deskEvaluation(): BelongsTo
    {
        return $this->belongsTo(DeskEvaluation::class, 'desk_evaluation_id');
    }

    public function kategoriTemuan(): BelongsTo
    {
        return $this->belongsTo(KategoriTemuan::class, 'kategori_temuan_id');
    }

    public function daftarTemuanPp(): HasMany
    {
        return $this->hasMany(DaftarTemuanPp::class, 'temuan_id');
    }
}
