<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisTemuan extends Model
{
    protected $table = 'jenis_temuan';

    protected $fillable = ['nama', 'status'];

    public function kategoriTemuan(): HasMany
    {
        return $this->hasMany(KategoriTemuan::class, 'jenis_temuan_id');
    }
}
