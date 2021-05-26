<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MainModel extends Model
{
    /**
     * @param $query
     * @return mixed
     */
    public function scopeByOwner($query)
    {
        return $query->where('user_id', (int)Auth::user()->getAuthIdentifier());
    }

    public function scopeGetById($model, $id)
    {
        return $model::find($id);
    }
}
