<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LaundryOrderController;
use App\Http\Controllers\API\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Your protected laundry routes
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/orders', [LaundryOrderController::class, 'index']);
    Route::post('/orders', [LaundryOrderController::class, 'store']);
    Route::put('/orders/{id}/status', [LaundryOrderController::class, 'updateStatus']);

});
