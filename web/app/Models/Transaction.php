<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use UuidTrait;
    use SoftDeletes;

    /**
     * @var array|string[]
     */
    public static array $rules = [
        'user_plan' => 'required|string|max:255',
        'reward' => 'integer',
        'currency' => 'string|max:5',
        'operation_name' => 'required|string|max:255',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'user_plan',
        'reward',
        'currency',
        'operation_name',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
