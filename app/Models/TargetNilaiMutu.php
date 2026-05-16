<?php

namespace App\Models;

use App\Models\Concerns\LogsMenuActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetNilaiMutu extends Model
{
    use LogsMenuActivity;

    protected $table = 'target_nilai_mutu';

    protected $fillable = ['pengaturan_periode_id', 'auditee_id', 'target_nilai'];

    public function pengaturanPeriode(): BelongsTo
    {
        return $this->belongsTo(PengaturanPeriode::class, 'pengaturan_periode_id');
    }

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'auditee_id');
    }
}
