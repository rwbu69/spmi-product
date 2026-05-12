<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandarMutu extends Model
{
    protected $table = 'standar_mutu';

    protected $fillable = [
        'kode',
        'nama_standar',
        'parent_id',
        'lembaga_akreditasi_id',
        'tahun_periode_id',
        'level',
        'data_dukung',
        'deskripsi',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(StandarMutu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(StandarMutu::class, 'parent_id');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function lembagaAkreditasi(): BelongsTo
    {
        return $this->belongsTo(LembagaAkreditasi::class, 'lembaga_akreditasi_id');
    }

    public function tahunPeriode(): BelongsTo
    {
        return $this->belongsTo(TahunPeriode::class, 'tahun_periode_id');
    }

    public function indikator(): HasMany
    {
        return $this->hasMany(Indikator::class, 'standar_mutu_id');
    }

    public function daftarKesesuaian(): HasMany
    {
        return $this->hasMany(DaftarKesesuaian::class, 'standar_mutu_id');
    }
}
