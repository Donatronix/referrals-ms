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
        $data = [];
        if ($format == 'week' || $format == 'month') {
            $cnt = $format == 'week' ? 7 : 30;
            for ($i = 1; $i <= $cnt; $i++) {
                $result[$i] = self::getDataForDateByFormat($user_id, 'day_data', $cnt - $i);
                if(!$result[$i]->isEmpty()){
                    foreach ($result[$i] as $k => $item) {
                        $data[$i]['date'] = $item->attributes['created_at'];
                        $data[$i][$k]['reward'] =+ $item->attributes['reward'];
                    }
                }
                else{
                    $data[$i]['reward'] = 0;
                    $data[$i]['date'] = Carbon::now()->subDay(30 - $i)->toDateTimeString();
                }
            }
        }

        if ($format == 'year') {
            for ($i = 1; $i <= 12; $i++) {
                $result[$i] = self::getDataForDateByFormat($user_id, 'month_data', 12 - $i);
                if(!$result[$i]->isEmpty()){
                    foreach ($result[$i] as $k => $item) {
                        $data[$i]['date'] = Carbon::now()->subMonth(12 - $i)->format('F');
                        $data[$i][$k]['reward'] =+ $item->attributes['reward'];
                    }
                }
                else{
                    $data[$i]['reward'] = 0;
                    $data[$i]['month'] = Carbon::now()->subMonth(12 - $i)->format('F');
                }
            }
        }
        return $data;
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
            case 'day_data':
                return self::select('reward', 'created_at')
                    ->where('user_id', $user_id)
                    ->whereDay('created_at', Carbon::now()->subDay($quantity))
                    ->get();

            case 'month_data':
                return self::select('reward', 'created_at')
                    ->where('user_id', $user_id)
                    ->whereMonth('created_at', Carbon::now()->subMonth($quantity))
                    ->get();
        }
    }

    public static function hideData($data)
    {
        foreach ($data as $item) {
            unset($item);
        }
    }
}
