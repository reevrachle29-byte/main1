<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueSession extends Model
{
    protected $primaryKey = 'session_id';

    protected $fillable = ['office_id', 'user_id', 'status', 'opened_at', 'closed_at'];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
