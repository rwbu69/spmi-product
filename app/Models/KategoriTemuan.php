<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriTemuan extends Model
{
    protected $table = 'kategori_temuan';

    protected $fillable = ['nama_kategori', 'jenis_temuan_id'];

    public function jenisTemuan(): BelongsTo
    {
        return $this->belongsTo(JenisTemuan::class, 'jenis_temuan_id');
    }

    public function temuan(): HasMany
    {
        return $this->hasMany(Temuan::class, 'kategori_temuan_id');
    }
}
