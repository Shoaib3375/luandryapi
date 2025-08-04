<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class OrderLog extends Model
{
    protected $fillable = ['order_id', 'admin_id', 'old_status', 'new_status'];

    protected static function booted()
    {
        static::created(function ($log) {
            Log::info('Order status change logged', [
                'order_id' => $log->order_id,
                'admin_id' => $log->admin_id,
                'old_status' => $log->old_status,
                'new_status' => $log->new_status
            ]);
        });
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class, 'order_id');
    }
}
