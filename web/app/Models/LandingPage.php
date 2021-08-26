<?php

namespace App\Models;

use App\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    use HasFactory;
    use OwnerTrait;

    protected $fillable = [
        'user_id',
        'template_id',
        'json' => '[]', // json array of user's texts
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
