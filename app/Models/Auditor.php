<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditor extends Model
{
    protected $table = 'auditor';

    protected $fillable = ['nama', 'email', 'keahlian', 'no_hp', 'keterangan'];

    public function deskEvaluasi(): HasMany
    {
        return $this->hasMany(DeskEvaluation::class, 'auditor_id');
    }
}
