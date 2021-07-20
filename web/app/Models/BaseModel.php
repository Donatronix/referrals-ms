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
    public function scopeByOwner($query)
    {
        return $query->where('user_id', (int)Auth::user()->getAuthIdentifier());
    }
}
