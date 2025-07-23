<?php

use App\Http\Controllers\API\AdminDashboardController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\LaundryOrderController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\OrderLogController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\TestController;
use App\Models\OrderLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Broadcasting\BroadcastController;



Route::get('/', function () {
    return response()->json(['message' => 'Welcome to eLaundry!'], 200);
});

// Debug endpoint for order logs
Route::get('/debug/orders/{id}/logs', function ($id) {
    $logs = DB::table('order_logs')
        ->where('order_id', $id)
        ->orderBy('created_at', 'desc')
        ->get();
        
    return response()->json([
        'order_id' => $id,
        'logs' => $logs,
        'count' => $logs->count()
    ]);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// User authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// User order routes
Route::middleware('auth:api')->group(function () {
    Route::get('/orders', [LaundryOrderController::class, 'index']);
    Route::post('/orders', [LaundryOrderController::class, 'store']);
    Route::get('/orders/filter', [LaundryOrderController::class, 'filterByStatus']);
    Route::put('/orders/{id}/cancel', [LaundryOrderController::class, 'cancelOrder']);
    Route::put('/orders/{id}/update', [LaundryOrderController::class, 'updateOrder']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/orders/{id}/logs', [OrderLogController::class, 'index']);
    Route::get('/orders/{id}/logs/check/{status}', [OrderLogController::class, 'checkStatusChange']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

// Admin-only routes
Route::middleware(['auth:api', 'is_admin'])->group(function () {
    Route::put('/orders/{id}/status', [LaundryOrderController::class, 'updateStatus']);
    Route::post('/test/order-log', [TestController::class, 'testOrderLog']);
    Route::post('/coupons', [CouponController::class, 'store']);
    Route::get('/coupons', [CouponController::class, 'index']);
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'stats']);
        Route::get('/revenue', [AdminDashboardController::class, 'revenueReport']);
    });
});

// User's own orders
Route::get('/my-orders', [LaundryOrderController::class, 'userOrders'])->middleware('auth:api');

// Broadcasting
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate'])->middleware('auth:api');
