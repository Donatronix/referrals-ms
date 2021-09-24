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

    /**
     *  Getting data for the graph
     *
     * @param string | $user_id
     * @param string | $format
     *
     * @return array | $result
     */
    public static function getDataForDate($user_id, $format)
    {
        $today = Carbon::now();
        if ($format == 'week' || $format == 'day') {
            $cnt = $format == 'week' ? $today->dayOfWeek : $today->day;

            for ($i = 0; $i < $cnt; $i++) {
                if ($i == 0) {
                    $result[$i] = self::getDataForDateByFormat($user_id, 'current_day_data');
                } else {
                    $result[$i] = self::getDataForDateByFormat($user_id, 'current_month_data', $i);
                }
            }
        }

        if ($format == 'month') {
            for ($i = 0; $i < $today->month; $i++) {
                if ($i == 0) {
                    $result[$i] = self::getDataForDateByFormat($user_id, 'current_month_data');
                } else {
                    $result[$i] = self::getDataForDateByFormat($user_id, 'other_month_data', $i);
                }
            }
        }

        return $result;
    }

    /**
     *  Get date data (for a week, for a month, for a year) by format
     *
     * @param      string | $user_id
     * @param      string | $format
     * @param null | integer | $quantity
     *
     * @return object
     */
    public static function getDataForDateByFormat($user_id, $format, $quantity = null)
    {
        switch ($format) {
            case 'current_day_data':
                $result = self::select('reward', 'created_at')
                    ->where('user_id', $user_id)
                    ->whereDay('created_at', Carbon::now()->day)
                    ->get();
                break;

            case 'other_day_data':
                $result = self::select('reward', 'created_at')
                    ->where('user_id', $user_id)
                    ->whereDay('created_at', Carbon::now()->subDay($quantity))
                    ->get();
                break;

            case 'current_month_data':
                $result = self::select('reward', 'created_at')
                    ->where('user_id', $user_id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->first();
                break;

            case 'other_month_data':
                $result = User::select('reward', 'created_at')
                    ->where('referrer_id', $user_id)
                    ->whereMonth('created_at', Carbon::now()->subMonth($quantity))
                    ->get();
        }
//            return $result;
            return $result === null ? null : $result;

    }

    public static function hideData($data)
    {
        foreach ($data as $item) {
            unset($item);
        }
    }
}
