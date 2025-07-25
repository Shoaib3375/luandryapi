<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    use ApiResponseTrait;

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

            return $this->successResponse($coupon, $message);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation error.', $e->errors(), 422);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to create coupon.', $e->getMessage(), 500);
        }
    }


    public function index(): JsonResponse
    {
        return $this->successResponse(Coupon::orderBy('expires_at', 'asc')->get(), 'Coupons fetched successfully.');
    }
    
    /**
     * Validate if a coupon is valid and return its details
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50',
            ]);
            
            $coupon = Coupon::where('code', $validated['code'])
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->first();
            
            if (!$coupon) {
                return $this->errorResponse(
                    'Invalid coupon', 
                    'The coupon code is invalid or has expired.', 
                    404
                );
            }
            
            return $this->successResponse([
                'code' => $coupon->code,
                'discount_percent' => $coupon->discount_percent,
                'expires_at' => $coupon->expires_at,
                'valid' => true
            ], 'Coupon is valid.');
            
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation error.', $e->errors(), 422);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to validate coupon.', $e->getMessage(), 500);
        }
    }
}
