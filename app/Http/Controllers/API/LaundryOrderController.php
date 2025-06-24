<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LaundryOrder;
use App\Models\OrderLog;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaundryOrderController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth:sanctum');
    }

    public function index(): JsonResponse
    {
        $orders = LaundryOrder::with('service')->where('user_id', auth()->id())->get();

        return response()->json($orders);
    }


    public function store(Request $request):JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|numeric|min:0.1',
            'note' => 'nullable|string|max:1000',
        ]);

        $order = LaundryOrder::create([
            'user_id' => auth()->id(),
            'service_id' => $request->service_id,
            'quantity' => $request->quantity,
            'note' => $request->note,
            'status' => 'Pending',

        ]);

        return response()->json([
            'message' => 'Order placed successfully!',
            'order' => $order
        ], 201);
    }
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:Pending,Processing,Completed,Cancelled',
        ]);

        $order = LaundryOrder::findOrFail($id);
        $oldStatus = $order->status;


        if (!auth()->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized — Admins only'], 403);
        }


        if ($oldStatus === $request->status) {
            return response()->json([
                'message' => 'Order status is already "' . $oldStatus . '". No changes made.',
            ], 200);
        }

        $order->status = $request->status;
        $order->save();
        $order->user->notify(new OrderStatusUpdated($order));

        OrderLog::create([
            'order_id' => $order->id,
            'admin_id' => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => $order->status,
        ]);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'order' => $order,
        ], 200);
    }


    public function filterByStatus(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:Pending,Processing,Completed,Cancelled',
        ]);

        $orders = LaundryOrder::with('user', 'service')
            ->where('status', $request->status)
            ->latest()
            ->get();

        return response()->json([
            'status' => $request->status,
            'orders' => $orders,
        ]);
    }


    public function cancelOrder($id): JsonResponse
    {
        $order = LaundryOrder::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->status !== 'Pending') {
            return response()->json([
                'message' => 'Only pending orders can be cancelled.',
            ], 400);
        }

        $order->status = 'Cancelled';
        $order->save();

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'order' => $order,
        ]);
    }


    public function updateOrder(Request $request, $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.1',
        ]);

        $order = LaundryOrder::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->status !== 'Pending') {
            return response()->json([
                'message' => 'Only pending orders can be updated.',
            ], 400);
        }

        $service = $order->service;
        $order->quantity = $request->quantity;
        $order->total_price = $service->price * $request->quantity;
        $order->save();

        return response()->json([
            'message' => 'Order updated successfully.',
            'order' => $order,
        ]);
    }
    public function userOrders(Request $request): JsonResponse
    {
        $orders = LaundryOrder::with('service')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10); // 👈 paginated

        return response()->json($orders);
    }


}
