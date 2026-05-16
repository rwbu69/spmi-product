<?php

namespace App\Models;

use App\Models\Concerns\LogsMenuActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitPenunjang extends Model
{
    use LogsMenuActivity;

    protected $table = 'unit_penunjang';

    protected $fillable = ['kode', 'nama_unit', 'auditee_pusat_id', 'jenjang', 'alamat', 'keterangan'];

    public function auditeePusat(): BelongsTo
    {
        return $this->belongsTo(AuditeePusat::class, 'auditee_pusat_id');
    }
}
