<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCode extends Model
{
    use HasFactory;
    const CAMPAIGN = 'Referral Program';
    const MEDIUM = 'Invite Friends';

    const ANDROID_PACKAGE_NAME = 'net.sumra.android';
    //const ANDROID_MIN_PACKAGE_VERSION = '20040902';

    protected $appends = ['resource_url'];

    protected $fillable = [
        'user_id',
        'package_name',
        'referral_link',
        'code',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function generate($user_id)
    {
        $exists = true;
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $r = rand(0,61);
                if($r<10) {
                    $code .= chr($r+0x30);
                } elseif($r<36) {
                    $code .= chr($r+0x41-10);
                } else {
                    $code .= chr($r+0x61-36);
                }
            }

            if($this->codeNotExists($code)) {
                $exists = false;
            }
        } while($exists);

        $this->user_id = $user_id;
        $this->code = $code;
        $this->save();
    }

    private function codeNotExists($code) : bool
    {
        $c = self::where('code',$code)->first();
        if($c) {
            return false;
        }
        return true;
    }

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/links/'.$this->getKey());
    }
}
