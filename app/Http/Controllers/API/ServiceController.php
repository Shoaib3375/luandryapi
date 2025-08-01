<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $services = ServiceResource::collection(Service::all());
        return $this->successResponse($services, 'Service list fetched successfully');
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'price' => 'required|numeric',
                'pricing_method' => 'required|in:per_kg,per_item,flat_rate',
                'price_per_unit' => 'nullable|numeric'
            ]);

            $service = Service::create($validated);

            return $this->successResponse(
                new ServiceResource($service),
                'Service created successfully',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::info('Service validation failed:', $e->errors());
            throw $e;
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $service = Service::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric',
            'pricing_method' => 'required|in:per_kg,per_item,flat_rate',
            'price_per_unit' => 'nullable|numeric'
        ]);

        $service->update($validated);

        return $this->successResponse(
            new ServiceResource($service),
            'Service updated successfully'
        );
    }

    public function destroy($id): JsonResponse
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return $this->successResponse(null, 'Service deleted successfully');
    }
}
