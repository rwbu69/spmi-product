<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name', 'username', 'email', 'password',
        'keterangan', 'is_active',
        'auditee_id', 'auditor_id', 'auditee_pusat_id', 'unit_penunjang_id',
    ];

    protected $hidden = ['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'username', 'password'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class);
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(Auditor::class);
    }

    public function auditeePusat(): BelongsTo
    {
        return $this->belongsTo(AuditeePusat::class);
    }

    public function unitPenunjang(): BelongsTo
    {
        return $this->belongsTo(UnitPenunjang::class);
    }
}
