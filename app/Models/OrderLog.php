<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OrderLog extends Model
{
    protected $fillable = ['order_id', 'admin_id', 'old_status', 'new_status'];
    
    protected static function booted()
    {
        static::created(function ($log) {
            // Log when a new status change is recorded
            Log::info('Order status change logged', [
                'order_id' => $log->order_id,
                'admin_id' => $log->admin_id,
                'old_status' => $log->old_status,
                'new_status' => $log->new_status
            ]);
        });
    }
    
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    
    public function order()
    {
        return $this->belongsTo(LaundryOrder::class, 'order_id');
    }
    
    public static function verifyStatusChange($orderId, $oldStatus, $newStatus)
    {
        return self::where('order_id', $orderId)
            ->where('old_status', $oldStatus)
            ->where('new_status', $newStatus)
            ->exists();
    }
}
