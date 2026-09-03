<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueRequest extends Model
{
    protected $primaryKey = 'request_id';
    
    // We disable standard timestamps because you used a custom 'requested_at' column
    public $timestamps = false; 

    protected $fillable = ['user_id', 'service_id', 'queue_number', 'tracking_code', 'status', 'category', 'requested_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function transaction()
    {
        return $this->hasOne(QueueTransaction::class, 'request_id');
    }
}