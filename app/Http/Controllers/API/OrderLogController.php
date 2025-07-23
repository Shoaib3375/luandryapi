<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OrderLog;
use App\Models\LaundryOrder;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderLogController extends Controller
{
    public function index($orderId): JsonResponse
    {
        // Get logs with direct query
        $logs = DB::table('order_logs')
            ->where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Format logs with admin details
        $formattedLogs = [];
        foreach ($logs as $log) {
            $admin = DB::table('users')
                ->where('id', $log->admin_id)
                ->first(['id', 'name', 'email']);
                
            $formattedLog = (array)$log;
            $formattedLog['admin'] = $admin ? (array)$admin : null;
            $formattedLogs[] = $formattedLog;
        }
        
        return response()->json([
            'order_id' => $orderId,
            'logs' => $formattedLogs
        ]);
    }
    
    public function checkStatusChange($orderId, $status): JsonResponse
    {
        // Find the order using direct query
        $order = DB::table('laundry_orders')->where('id', $orderId)->first();
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => "Order not found"
            ], 404);
        }
        
        // Check if there's a log entry for this status change using direct query
        $log = DB::table('order_logs')
            ->where('order_id', $orderId)
            ->where('new_status', $status)
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($log) {
            $admin = DB::table('users')->where('id', $log->admin_id)->first(['id', 'name', 'email']);
            
            return response()->json([
                'success' => true,
                'message' => "Status change to {$status} was logged",
                'log' => $log,
                'admin' => $admin
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => "No log found for status change to {$status}"
        ]);
    }
}
