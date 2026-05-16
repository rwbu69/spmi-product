<?php

namespace App\Models;

use App\Models\Concerns\LogsMenuActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiMutu extends Model
{
    use LogsMenuActivity;

    protected $table = 'nilai_mutu';

    protected $fillable = ['auditee_id', 'pengaturan_periode_id', 'lembaga_akreditasi_id', 'nilai', 'keterangan'];

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }

    public function pengaturanPeriode(): BelongsTo
    {
        return $this->belongsTo(PengaturanPeriode::class, 'pengaturan_periode_id');
    }

    public function lembagaAkreditasi(): BelongsTo
    {
        return $this->belongsTo(LembagaAkreditasi::class, 'lembaga_akreditasi_id');
    }
}
