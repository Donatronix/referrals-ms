<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $appends = [
        'resource_url'
    ];

    protected $fillable = [
        'name',
        'device_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/devices/'.$this->getKey());
    }
}
