<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OrderLog;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function testOrderLog(Request $request): JsonResponse
    {
        try {
            $log = OrderLog::create([
                'order_id' => $request->order_id,
                'admin_id' => auth()->id(),
                'old_status' => 'Pending',
                'new_status' => 'Completed',
            ]);

            Log::info('Test order log created', [
                'log_id' => $log->id,
                'order_id' => $request->order_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test order log created',
                'log' => $log
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create test order log', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create test order log',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
