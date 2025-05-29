<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LaundryOrder;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaundryOrderController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $orders = LaundryOrder::with('service')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|numeric|min:0.1',
        ]);

        $service = Service::findOrFail($data['service_id']);

        // Calculate total price based on pricing method
        $totalPrice = $data['quantity'] * $service->price;

        $order = LaundryOrder::create([
            'user_id' => Auth::id(),
            'service_id' => $service->id,
            'quantity' => $data['quantity'],
            'total_price' => $totalPrice,
        ]);

        return response()->json([
            'message' => 'Order placed successfully',
            'order' => $order,
        ]);
    }
}
