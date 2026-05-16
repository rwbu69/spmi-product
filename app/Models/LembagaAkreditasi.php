<?php

namespace App\Models;

use App\Models\Concerns\LogsMenuActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LembagaAkreditasi extends Model
{
    use LogsMenuActivity;

    protected $table = 'lembaga_akreditasi';

    protected $fillable = ['nama_lembaga', 'keterangan'];

    public function standarMutu(): HasMany
    {
        return $this->hasMany(StandarMutu::class, 'lembaga_akreditasi_id');
    }

    public function pengaturanPeriode(): HasMany
    {
        return $this->hasMany(PengaturanPeriode::class, 'lembaga_akreditasi_id');
    }

    public function nilaiMutu(): HasMany
    {
        return $this->hasMany(NilaiMutu::class, 'lembaga_akreditasi_id');
    }
}
