<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $appends = [
        'resource_url'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
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
