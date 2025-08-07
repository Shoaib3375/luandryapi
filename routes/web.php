<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

Route::get('/', function () {
    print("Hello");
});
Route::get('/test-redis', function () {
    try {
        Cache::put('redis-test', 'Redis is working!', 60);
        $value = Cache::get('redis-test');

        return response()->json([
            'status' => 'success',
            'message' => $value,
            'cache_driver' => config('cache.default'),
            'redis_connection' => 'active'
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

