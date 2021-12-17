<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $casts = [
        'metadata' => 'object'
    ];

    protected $fillable = [
        'title',
        'html', // html or react template
        'metadata'
    ];
}
