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
        'name', 'username', 'password',
        'keterangan', 'is_active',
        'auditee_id', 'auditor_id', 'auditee_pusat_id', 'unit_penunjang_id',
    ];

    protected $hidden = ['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'username']);
    }

    public function beforeActivityLogged(\Illuminate\Database\Eloquent\Model $activity, string $eventName)
    {
        $props = $activity->properties ? $activity->properties->toArray() : [];
        unset($props['ip']);
        unset($props['user_agent']);
        $activity->properties = collect($props);

        if ($eventName === 'created') {
            $activity->data_lama = null;
            $activity->data_baru = json_encode($this->getAttributes());
        } elseif ($eventName === 'updated') {
            $activity->data_lama = json_encode($this->getOriginal());
            $activity->data_baru = json_encode($this->getAttributes());
        } elseif ($eventName === 'deleted') {
            $activity->data_lama = json_encode($this->getOriginal() ?: $this->getAttributes());
            $activity->data_baru = null;
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
