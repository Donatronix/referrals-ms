<?php


namespace Sumra\PubSub\Models;


use Illuminate\Database\Eloquent\Model;

class AbstractMessage extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_SENT = 1;
    const STATUS_DONE = 2;

    protected $casts = [
        'message' => 'array',
    ];

    protected $fillable = [
        'uniq_id', 'queue', 'event', 'message', 'properties'
    ];
}
