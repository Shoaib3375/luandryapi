<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    protected $fillable = ['order_id', 'admin_id', 'old_status', 'new_status'];
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

}
