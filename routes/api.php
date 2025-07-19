<?php

use App\Http\Controllers\API\AdminDashboardController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\LaundryOrderController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ServiceController;
use App\Models\OrderLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Broadcasting\BroadcastController;



Route::get('/', function () {
    return response()->json(['message' => 'Welcome to eLaundry!'], 200);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:api')->group(function () {


    Route::get('/orders', [LaundryOrderController::class, 'index']);
    Route::post('/orders', [LaundryOrderController::class, 'store']);
    Route::get('/orders/filter', [LaundryOrderController::class, 'filterByStatus']);
    Route::put('/orders/{id}/cancel', [LaundryOrderController::class, 'cancelOrder']);
    Route::put('/orders/{id}/update', [LaundryOrderController::class, 'updateOrder']);

    Route::get('/orders/{id}/logs', function ($id) {
        return OrderLog::where('order_id', $id)->with('admin')->latest()->get();
    });
    Route::get('/services', [ServiceController::class, 'index']);
});


Route::middleware(['auth:api', 'is_admin'])->group(function () {
    Route::put('/orders/{id}/status', [LaundryOrderController::class, 'updateStatus']);
    Route::post('/coupons', [CouponController::class, 'store']);
    Route::get('/coupons', [CouponController::class, 'index']);
});

Route::get('/my-orders', [LaundryOrderController::class, 'userOrders'])->middleware('auth:api');



Route::middleware('auth:api')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});



Route::middleware(['auth:api'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'stats']);
    Route::get('/revenue', [AdminDashboardController::class, 'revenueReport']);
});

Route::get('/orders/{id}/logs', function ($id) {
    return OrderLog::where('order_id', $id)->with('admin')->latest()->get();
})->middleware('auth:api');

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcasting authentication endpoint
Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate'])->middleware('auth:api');

