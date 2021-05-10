<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Landingpage extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'json' => '[]', // json array of user's texts
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

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

}
