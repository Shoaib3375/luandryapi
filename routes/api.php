<?php

use App\Http\Controllers\API\AdminDashboardController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\LaundryOrderController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\OrderLogController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\TestController;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Support\Facades\Route;

// Welcome endpoint
Route::get('/', function () {
    return response()->json(['message' => 'Welcome to eLaundry!'], 200);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/services', [ServiceController::class, 'index']);
Route::post('/guest/orders', [LaundryOrderController::class, 'guestOrder']);

// Authenticated user routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth endpoints
    Route::get('/user', fn(\Illuminate\Http\Request $request) => $request->user());
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Orders
    Route::get('/orders', [LaundryOrderController::class, 'index']);
    Route::get('/orders/{id}', [LaundryOrderController::class, 'show']);
    Route::post('/orders', [LaundryOrderController::class, 'store']);
    Route::get('/orders/filter', [LaundryOrderController::class, 'filterByStatus']);
    Route::put('/orders/{id}/cancel', [LaundryOrderController::class, 'cancelOrder']);
    Route::put('/orders/{id}/update', [LaundryOrderController::class, 'updateOrder']);
    Route::get('/my-orders', [LaundryOrderController::class, 'userOrders']);

    // Order logs
    Route::get('/orders/{id}/logs', [OrderLogController::class, 'index']);
    Route::get('/orders/{id}/logs/check/{status}', [OrderLogController::class, 'checkStatusChange']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Coupons
    Route::post('/coupons/validate', [CouponController::class, 'validate']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/addresses', [ProfileController::class, 'addAddress']);
    Route::put('/profile/addresses/{id}', [ProfileController::class, 'updateAddress']);
    Route::delete('/profile/addresses/{id}', [ProfileController::class, 'deleteAddress']);

    // Broadcasting
    Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate']);
});

// Admin routes
Route::middleware('auth:sanctum')->middleware('is_admin')->group(function () {
    // Order management
    Route::put('/orders/{id}/status', [LaundryOrderController::class, 'updateStatus']);

    // Service management
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

    // Coupon management
    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/coupons', [CouponController::class, 'store']);

    // Admin dashboard
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'stats']);
        Route::get('/revenue', [AdminDashboardController::class, 'revenueReport']);
        Route::get('/orders/count', [AdminDashboardController::class, 'countAllOrders']);
    });

    // Testing
    Route::post('/test/order-log', [TestController::class, 'testOrderLog']);
});
