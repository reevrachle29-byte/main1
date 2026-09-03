<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Office extends Model
{
    use HasFactory;

    // Custom Primary Key
    protected $primaryKey = 'office_id';

    protected $fillable = [
        'name',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the staff/admin assigned to this office.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get all services offered by this office.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'office_id', 'office_id');
    }

    /**
     * Get all queue requests assigned to this office (through services).
     */
    public function queueRequests(): HasManyThrough
    {
        return $this->hasManyThrough(QueueRequest::class, Service::class, 'office_id', 'service_id');
    }
}