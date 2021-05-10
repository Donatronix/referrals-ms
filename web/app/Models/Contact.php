<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Contact extends Model
{
    protected $fillable = [
        'commonid',
        'user_id',
        'firstname',
        'lastname',
        'middlename',
        'prefix',
        'suffix',
        'nickname',
        'adrpob',
        'adrextend',
        'adrstreet',
        'adrcity',
        'adrstate',
        'adrzip',
        'adrcountry',
        'tel1',
        'tel2',
        'email'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public $incrementing = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function save(array $options=[])
    {
        if($options['user_id']) {
            $user_id = $options['user_id'];
        } else {
            $user_id = intval(Auth::user()->getAuthIdentifier());
        }
        $contact = self::select("1")->where('user_id', $user_id)->where(function($query) use ($options) {
            $query->orWhere('tel1', $options['tel1'])
                ->orWhere('tel2', $options['tel2'])
                ->orWhere('tel1', $options['tel1'])
                ->orWhere('tel2', $options['tel2']);
        })->first();
        if($contact) {
            // contact already exists
            return false;
        }
        // check for same contact from other users
        $common = self::select('commonid')->where(function($query) use ($options) {
            $query->orWhere('tel1', $options['tel1'])
                ->orWhere('tel2', $options['tel2'])
                ->orWhere('tel1', $options['tel1'])
                ->orWhere('tel2', $options['tel2']);
        })->first();
        if($common) {
            $options['commonid'] = $common->commonid;
        }
        return parent::save($options);
    }

}
