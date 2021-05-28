<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Refcode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
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
}
