<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OrderLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderLogController extends Controller
{
    public function index($orderId): JsonResponse
    {
        $logs = OrderLog::with('admin')
            ->where('order_id', $orderId)
            ->latest()
            ->get();

        return response()->json([
            'order_id' => $orderId,
            'logs' => $logs,
        ]);
    }
}
