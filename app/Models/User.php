<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'phone',
        'branch_id',
        'must_change_password',
        'status',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'last_login_at',
        'last_login_ip',
        'failed_login_count',
        'locked_until',
        'chat_status_emoji',
        'chat_status_text',
        'chat_status_until',
        'chat_dnd_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at'       => 'datetime',
        'password'                => 'hashed',
        'must_change_password'    => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
        'last_login_at'           => 'datetime',
        'locked_until'            => 'datetime',
        'failed_login_count'      => 'integer',
        'chat_status_until'       => 'datetime',
        'chat_dnd_until'          => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function kpiAlerts()
    {
        return $this->hasMany(KpiAlert::class, 'recipient_id');
    }

    public function kpiNotes()
    {
        return $this->hasMany(KpiNote::class, 'author_id');
    }

    public function managedStaff()
    {
        return $this->hasMany(StaffProfile::class, 'manager_id');
    }

    // Helpers
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function getFirstNameAttribute(): string
    {
        return explode(' ', $this->name)[0] ?? $this->name;
    }
}
