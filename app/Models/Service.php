<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'category', 'price', 'pricing_method', 'price_per_unit'];

    public function orders(): hasMany
    {
        return $this->hasMany(LaundryOrder::class);
    }
}
