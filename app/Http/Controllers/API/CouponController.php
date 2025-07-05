<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|unique:coupons,code|max:50',
                'discount_percent' => 'required|numeric|min:1|max:100',
                'expires_at' => 'nullable|date|after:today',
            ]);

            $coupon = Coupon::create($validated);

            $message = 'Coupon created successfully.';

            if (!$coupon->expires_at || $coupon->expires_at > now()->toDateString()) {
                $message .= ' This coupon is currently active.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $coupon
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create coupon.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Coupons fetched successfully.',
            'data' => Coupon::orderBy('expires_at', 'asc')->get(),
        ]);

    }
}
