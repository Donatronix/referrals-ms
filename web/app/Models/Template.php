<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'title', // title
        'html', // html or react template
        'json' => '[]', // default json array of changeable texts
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;


}
