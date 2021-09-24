<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Carbon\Carbon;
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

    public static function getDataForDate($user_id, $format)
    {
        $today = Carbon::now();
        if($format == 'week')
        {
            for ($i=0; $i < $today['dayOfWeek']; $i++)
            {
                if ( $i == 0 ){
                    self::getDataForDateByFormat($user_id, 'current_day_data');
                }
                else{
                    self::getDataForDateByFormat($user_id, 'current_month_data');
                }
            }
        }
    }

    public static function getDataForDateByFormat($user_id, $format, $quantity = 1)
    {
        switch ($format)
        {
            case 'current_day_data':
                return self::where('user_id', $user_id)
                    ->whereDay('created_at',Carbon::now()->day)
                    ->get();

            case 'other_day_data':
                return self::where('user_id', $user_id)
                    ->whereDay('created_at',Carbon::now()->day)
                    ->get();

            case 'current_month_data':
                return self::where('user_id', $user_id)
                    ->whereMonth('created_at',Carbon::now()->month)
                    ->get();

            case 'last_month_data':
                return User::where('referrer_id', $user_id)
                    ->whereMonth('created_at',Carbon::now()->subMonth($quantity))
                    ->get();
        }
    }
}
