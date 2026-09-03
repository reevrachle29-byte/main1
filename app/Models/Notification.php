<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'notif_id';
    public $timestamps = false; // Using custom sent_at

    protected $fillable = ['transaction_id', 'user_id', 'type', 'message', 'sent_at'];

    public function transaction()
    {
        return $this->belongsTo(QueueTransaction::class, 'transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}