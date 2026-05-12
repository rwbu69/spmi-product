<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunPeriode extends Model
{
    protected $table = 'tahun_periode';

    protected $fillable = ['tahun', 'status'];

    public function pengaturanPeriode(): HasMany
    {
        return $this->hasMany(PengaturanPeriode::class, 'tahun_periode_id');
    }

    public function standarMutu(): HasMany
    {
        return $this->hasMany(StandarMutu::class, 'tahun_periode_id');
    }

    public function uploadLaporanRtm(): HasMany
    {
        return $this->hasMany(UploadLaporanRtm::class, 'tahun_periode_id');
    }

    public function isAktif(): bool
    {
        return $this->status === 'Aktif';
    }
}
