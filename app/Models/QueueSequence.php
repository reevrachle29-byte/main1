<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueSequence extends Model
{
    protected $table = 'queue_sequences';

    protected $primaryKey = 'sequence_date';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['sequence_date', 'last_number'];
}