<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueTransaction extends Model
{
    protected $primaryKey = 'transaction_id';
    
    // Disable timestamps since migration doesn't include created_at/updated_at
    public $timestamps = false;

    protected $fillable = ['request_id', 'served_by', 'called_at', 'completed_at', 'wait_minutes'];

    public function queueRequest()
    {
        return $this->belongsTo(QueueRequest::class, 'request_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'transaction_id');
    }
}