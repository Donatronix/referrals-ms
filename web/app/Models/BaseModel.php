<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BaseModel extends Model
{
    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeByOwner($query, $user_id = null)
    {
        return $query->where('user_id', $user_id ?? Auth::user()->getAuthIdentifier());
    }
}
