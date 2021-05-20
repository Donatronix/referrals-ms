<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'level',    // enum of ['basic', 'bronze', 'silver', 'gold']
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
    public $incrementing = false;

    public function userReferalcodes()
    {
        return $this->hasMany(ReferralCode::class);
    }
}
