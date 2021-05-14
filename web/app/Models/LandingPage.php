<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'json' => '[]', // json array of user's texts
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
