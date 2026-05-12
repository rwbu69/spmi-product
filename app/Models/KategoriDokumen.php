<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriDokumen extends Model
{
    protected $table = 'kategori_dokumen';

    protected $fillable = ['nama_kategori'];

    public function jenisDokumen(): HasMany
    {
        return $this->hasMany(JenisDokumen::class, 'kategori_dokumen_id');
    }
}

