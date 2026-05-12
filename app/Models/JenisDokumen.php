<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisDokumen extends Model
{
    protected $table = 'jenis_dokumen';

    protected $fillable = ['nama_jenis', 'kategori_dokumen_id'];

    public function kategoriDokumen(): BelongsTo
    {
        return $this->belongsTo(KategoriDokumen::class, 'kategori_dokumen_id');
    }

    public function manajemenDokumen(): HasMany
    {
        return $this->hasMany(ManajemenDokumen::class, 'jenis_dokumen_id');
    }
}
