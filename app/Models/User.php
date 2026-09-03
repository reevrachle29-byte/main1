<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id'; // Explicitly set for Capstone Custom Schema

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'contact', // Added for Capstone (FR-UM-05)
        'role',    // Added for Capstone (FR-UM-04)
        'office_id', // Added for assigned office staff (FR-OM-05)
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | Role Helper Methods (FR-UM-04)
    |--------------------------------------------------------------------------
    */

    public function setRoleAttribute($value): void
    {
        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['administrator', 'admin'], true)) {
            $this->attributes['role'] = 'admin';
            return;
        }

        if (in_array($normalized, ['employee', 'staff'], true)) {
            $this->attributes['role'] = 'staff';
            return;
        }

        if ($normalized === 'student') {
            $this->attributes['role'] = 'student';
            return;
        }

        $this->attributes['role'] = $normalized ?: 'student';
    }

    public function isAdmin(): bool
    {
        return in_array(strtolower($this->role ?? ''), ['administrator', 'admin'], true);
    }

    public function isEmployee(): bool
    {
        return in_array(strtolower($this->role ?? ''), ['employee', 'staff'], true);
    }

    public function isStudent(): bool
    {
        return strtolower($this->role ?? '') === 'student';
    }

    public function recoveryCodes(): array
    {
        if (empty($this->two_factor_recovery_codes)) {
            return [];
        }

        $codes = decrypt($this->two_factor_recovery_codes);

        if (is_array($codes)) {
            return $codes;
        }

        $decoded = json_decode($codes, true);

        return is_array($decoded) ? $decoded : [];
    }

    /*
    |--------------------------------------------------------------------------
    | Capstone Relationship Mappings
    |--------------------------------------------------------------------------
    */

    // FR-OM-05 / FR-OM-07: Office Personnel assignment
    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function queueRequests()
    {
        return $this->hasMany(QueueRequest::class, 'user_id');
    }

    public function transactionsServed()
    {
        return $this->hasMany(QueueTransaction::class, 'served_by');
    }
}