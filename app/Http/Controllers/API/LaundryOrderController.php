<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\LaundryOrderResource;
use App\Models\LaundryOrder;
use App\Models\OrderLog;
use App\Models\Service;
use App\Models\Coupon;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Throwable;

class LaundryOrderController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_admin) {
            $orders = LaundryOrder::with('service')->latest()->paginate(10);
        } else {
            $orders = LaundryOrder::where('user_id', $user->id)
                ->with('service')
                ->latest()
                ->paginate(10);
        }
        return response()->json([
            'data' => $orders,
            'message' => 'Order list',
            'status' => true,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
                'quantity' => 'required|numeric|min:0.1',
                'note' => 'nullable|string|max:1000',
                'coupon_code' => 'nullable|string'
            ]);

            $service = Service::findOrFail($validated['service_id']);
            $total = $service->pricing_method === 'weight'
                ? $service->price * floatval($validated['quantity'])
                : $service->price * intval($validated['quantity']);

            if ($request->filled('coupon_code')) {
                $coupon = Coupon::where('code', $request->coupon_code)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })->first();

                if ($coupon) {
                    $total -= ($total * $coupon->discount_percent / 100);
                }
            }

            $order = LaundryOrder::create([
                'user_id'        => auth()->id(),
                'service_id'     => $validated['service_id'],
                'quantity'       => $validated['quantity'],
                'note'           => $validated['note'] ?? null,
                'total_price'    => $total,
                'status'         => 'Pending',
                'payment_status' => 'Unpaid',
            ]);

            return $this->successResponse(new LaundryOrderResource($order), 'Order placed successfully', 201);

        } catch (\Throwable $e) {
            return $this->exceptionResponse($e, 'Failed to place order.');
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,Processing,Completed,Cancelled',
        ]);

        $order = LaundryOrder::findOrFail($id);
        $oldStatus = $order->status;

        if (!auth()->user()->is_admin) {
            return $this->errorResponse('Unauthorized — Admins only', 403);
        }

        if ($oldStatus === $validated['status']) {
            return $this->successResponse(null, "Order already in status: \"$oldStatus\"", 200);
        }

        $order->status = $validated['status'];
        $order->save();

        $order->user->notify(new OrderStatusUpdated($order));


        OrderLog::create([
            'order_id'   => $order->id,
            'admin_id'   => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => $order->status,
        ]);

        return $this->successResponse(new LaundryOrderResource($order), 'Order status updated successfully');
    }

    public function filterByStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,Processing,Completed,Cancelled',
        ]);

        $orders = LaundryOrder::with('user', 'service')
            ->where('status', $validated['status'])
            ->latest()
            ->get();

        return $this->successResponse(LaundryOrderResource::collection($orders), 'Filtered orders by status');
    }

    public function cancelOrder($id): JsonResponse
    {
        try {
            $order = LaundryOrder::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            if ($order->status !== 'Pending') {
                return $this->errorResponse('Only pending orders can be cancelled.', 400);
            }

            $order->status = 'Cancelled';
            $order->save();

            return $this->successResponse(new LaundryOrderResource($order), 'Order cancelled successfully.');

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('You are not authorized to cancel this order or it does not exist.', 403);
        } catch (Throwable $e) {
            return $this->exceptionResponse($e, 'Something went wrong while cancelling the order.');
        }
    }


    public function updateOrder(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|numeric|min:0.1',
            ]);

            $query = LaundryOrder::where('id', $id)->with('service');

            if (!auth()->user()->is_admin) {
                $query->where('user_id', auth()->id());
            }

            $order = $query->firstOrFail();

            if ($order->status !== 'Pending') {
                return $this->errorResponse('Only pending orders can be updated.', 400);
            }

            $service = $order->service;
            $quantity = $validated['quantity'];

            $baseTotal = $service->pricing_method === 'weight'
                ? $service->price * floatval($quantity)
                : $service->price * intval($quantity);

            $discountedTotal = $baseTotal;

            if (!empty($order->coupon_code)) {
                $coupon = Coupon::where('code', $order->coupon_code)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })->first();

                if ($coupon) {
                    $discountedTotal -= ($discountedTotal * $coupon->discount_percent / 100);
                }
            }

            $order->quantity = $quantity;
            $order->total_price = $discountedTotal;
            $order->save();

            return $this->successResponse(new LaundryOrderResource($order), 'Order updated successfully.');

        } catch (ModelNotFoundException  $e) {
            return $this->errorResponse('Order not found or unauthorized.', 404);
        }
    }

    public function userOrders(Request $request): JsonResponse
    {
        $orders = LaundryOrder::with('service')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->successResponse(LaundryOrderResource::collection($orders), 'User orders with pagination');
    }
}
