<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_STAFF = 'staff';
    public const TYPE_PROFESSIONAL = 'professional';

    protected $fillable = ['name', 'email', 'mobile', 'user_type', 'status', 'password', 'email_verified_at', 'last_login_at'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function professional(): HasOne
    {
        return $this->hasOne(Professional::class);
    }

    public function assignedApplications(): HasMany
    {
        return $this->hasMany(Application::class, 'assigned_staff_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function isCustomer(): bool
    {
        return $this->user_type === self::TYPE_CUSTOMER;
    }

    /** Staff and professionals both work in the admin panel. */
    public function isBackoffice(): bool
    {
        return in_array($this->user_type, [self::TYPE_STAFF, self::TYPE_PROFESSIONAL], true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function initials(): string
    {
        return collect(explode(' ', trim($this->name)))->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    }

    public function scopeBackoffice($query)
    {
        return $query->whereIn('user_type', [self::TYPE_STAFF, self::TYPE_PROFESSIONAL]);
    }
}
