<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    public function show(): JsonResponse
    {
        $user = auth()->user()->load(['addresses', 'defaultAddress']);
        
        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at,
            ],
            'addresses' => $user->addresses,
            'default_address' => $user->defaultAddress,
        ], 'Profile retrieved successfully');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
        ]);

        auth()->user()->update($validated);

        return $this->successResponse(null, 'Profile updated successfully');
    }

    public function addAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:home,work,other',
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            UserAddress::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        $address = UserAddress::create([
            'user_id' => auth()->id(),
            ...$validated
        ]);

        return $this->successResponse($address, 'Address added successfully', 201);
    }

    public function updateAddress(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:home,work,other',
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'boolean',
        ]);

        $address = UserAddress::where('user_id', auth()->id())->findOrFail($id);

        if ($validated['is_default'] ?? false) {
            UserAddress::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        $address->update($validated);

        return $this->successResponse($address, 'Address updated successfully');
    }

    public function deleteAddress($id): JsonResponse
    {
        $address = UserAddress::where('user_id', auth()->id())->findOrFail($id);
        $address->delete();

        return $this->successResponse(null, 'Address deleted successfully');
    }
}