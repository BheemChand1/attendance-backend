<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role_id',
        'phone',
        'employee_code',
        'is_active',
    ];

    /**
     * Hidden fields
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /* ============================
       Relationships
    ============================ */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /* ============================
       Role Helper Methods
    ============================ */

    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === 'superadmin';
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role?->slug === 'company_admin';
    }

    public function isHR(): bool
    {
        return $this->role?->slug === 'hr';
    }

    public function isEmployee(): bool
    {
        return $this->role?->slug === 'employee';
    }
}
