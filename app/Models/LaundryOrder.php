<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaundryOrder extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'quantity',
        'total_price',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            $service = \App\Models\Service::find($order->service_id);
            if ($service) {
                $order->total_price = $service->price * $order->quantity;
            }
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
