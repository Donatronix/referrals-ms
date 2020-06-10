<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoKey extends Model
{
    protected $fillable = [
        'version_key',
        'cipher',
        'cipher_key',
    ];
}
