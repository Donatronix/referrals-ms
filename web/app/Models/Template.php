<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', // title
        'html', // html or react template
        'json' => '[]', // default json array of changeable texts
    ];
}
