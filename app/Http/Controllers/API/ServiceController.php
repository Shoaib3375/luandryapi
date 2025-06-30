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
}
