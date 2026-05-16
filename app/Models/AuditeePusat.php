<?php

namespace App\Models;

use App\Models\Concerns\LogsMenuActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditeePusat extends Model
{
    use LogsMenuActivity;

    protected $table = 'auditee_pusat';

    protected $fillable = ['nama'];

    public function auditee(): HasMany
    {
        return $this->hasMany(Auditee::class, 'auditee_pusat_id');
    }

    public function unitPenunjang(): HasMany
    {
        return $this->hasMany(UnitPenunjang::class, 'auditee_pusat_id');
    }
}
