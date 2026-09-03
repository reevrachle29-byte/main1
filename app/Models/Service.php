<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $primaryKey = 'service_id';

    protected $fillable = ['office_id', 'user_id', 'service_name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function queueRequests()
    {
        return $this->hasMany(QueueRequest::class, 'service_id');
    }
}