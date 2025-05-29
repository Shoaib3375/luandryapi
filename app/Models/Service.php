<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'category', 'price', 'pricing_method'];

    public function orders()
    {
        return $this->hasMany(LaundryOrder::class);
    }
}
