<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationKey extends Model
{
    protected $appends = [
        'resource_url'
    ];

    protected $fillable = [
        'version_key',
        'cipher',
        'cipher_key'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /* ************************ ACCESSOR ************************* */
    public function getResourceUrlAttribute()
    {
        return url('/admin/application-keys/' . $this->getKey());
    }
}
