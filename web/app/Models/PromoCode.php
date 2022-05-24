<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sumra\SDK\Traits\UuidTrait;

class PromoCode extends Model
{
    use HasFactory;
    use UuidTrait;

    protected $fillable = [
        'user_id',
        'code',
    ];
}
