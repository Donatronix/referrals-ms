<?php

namespace App\Models;

use App\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    use HasFactory;
    use OwnerTrait;

    protected $casts = [
      'metadata' => 'object'
    ];

    protected $fillable = [
        'user_id',
        'template_id',
        'metadata'
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
