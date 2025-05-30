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
        $orders = LaundryOrder::with('service')->where('user_id', auth()->id())->get();

        return response()->json($orders);
    }


    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|numeric|min:0.1',
        ]);

        $order = LaundryOrder::create([
            'user_id' => auth()->id(),
            'service_id' => $request->service_id,
            'quantity' => $request->quantity,
            'status' => 'Pending', // Optional: default in DB
        ]);

        return response()->json([
            'message' => 'Order placed successfully!',
            'order' => $order
        ], 201);
    }
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Processing,Completed,Cancelled',
        ]);

        $order = LaundryOrder::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Order status updated',
            'order' => $order,
        ]);
    }

}
