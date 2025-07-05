<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'discount_percent', 'expires_at'];
    protected $casts = [
        'expires_at' => 'datetime',
        'discount_percent' => 'float',
    ];
}
