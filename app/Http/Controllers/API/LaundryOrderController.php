<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\LaundryOrderResource;
use App\Models\LaundryOrder;
use App\Models\OrderLog;
use App\Models\Service;
use App\Models\Coupon;
use App\Notifications\OrderStatusUpdated;
use App\Events\OrderStatusUpdated as OrderStatusUpdatedEvent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class LaundryOrderController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_admin) {
            $orders = LaundryOrder::with(['service', 'user'])->latest()->paginate(10);
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

    private function applyCouponDiscount($total, $couponCode)
    {
        if (!$couponCode) return ['total' => $total, 'coupon_applied' => false];

        $coupon = Coupon::where('code', $couponCode)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->first();

        if ($coupon) {
            $discountAmount = $total * $coupon->discount_percent / 100;
            return [
                'total' => $total - $discountAmount,
                'coupon_applied' => true,
                'discount_percent' => $coupon->discount_percent,
                'discount_amount' => $discountAmount
            ];
        }

        return ['total' => $total, 'coupon_applied' => false];
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
            $baseTotal = $service->pricing_method === 'weight'
                ? $service->price * floatval($validated['quantity'])
                : $service->price * intval($validated['quantity']);

            $couponResult = $this->applyCouponDiscount($baseTotal, $validated['coupon_code'] ?? null);
            $total = $couponResult['total'];

            $order = LaundryOrder::create([
                'user_id'        => auth()->id(),
                'service_id'     => $validated['service_id'],
                'quantity'       => $validated['quantity'],
                'note'           => $validated['note'] ?? null,
                'total_price'    => $total,
                'status'         => 'Pending',
                'payment_status' => 'Unpaid',
                'coupon_code'    => $validated['coupon_code'] ?? null,
            ]);

            $message = 'Order placed successfully';
            if ($couponResult['coupon_applied']) {
                $message .= " with {$couponResult['discount_percent']}% discount applied";
            }

            return $this->successResponse(new LaundryOrderResource($order), $message, 201);

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

        // Update order status
        $order->status = $validated['status'];
        $order->save();

        // Create order log
        OrderLog::create([
            'order_id' => $order->id,
            'admin_id' => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => $validated['status']
        ]);

        // Send notification to database
        $order->load('user');
        $order->user->notify(new OrderStatusUpdated($order));
        dd(get_class(auth()->user()));


        // Broadcast event
        event(new OrderStatusUpdatedEvent("Your order #{$order->id} status updated to {$order->status}", $order->user_id));

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
            ->paginate(10);

        return $this->successResponse(LaundryOrderResource::collection($orders), 'Filtered orders by status');
    }

    public function cancelOrder($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $query = LaundryOrder::where('id', $id);

            // If not admin, restrict to user's own orders
            if (!$user->is_admin) {
                $query->where('user_id', $user->id);
            }

            $order = $query->firstOrFail();
            $oldStatus = $order->status;

            if ($order->status !== 'Pending') {
                return $this->errorResponse('Only pending orders can be cancelled.', 400);
            }

            $order->status = 'Cancelled';
            $order->save();

            // Log the cancellation
            OrderLog::create([
                'order_id' => $order->id,
                'admin_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => 'Cancelled'
            ]);

            // Send notification only if admin cancelled the order
            if ($user->is_admin && $order->user_id !== $user->id) {
                $order->load('user');
                $order->user->notify(new OrderStatusUpdated($order));
                dd(get_class(auth()->user()));
            }

            // Broadcast event
            event(new OrderStatusUpdatedEvent("Your order #{$order->id} status updated to {$order->status}", $order->user_id));

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
                'coupon_code' => 'nullable|string'
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

            $couponCode = $validated['coupon_code'] ?? $order->coupon_code;
            $couponResult = $this->applyCouponDiscount($baseTotal, $couponCode);
            $discountedTotal = $couponResult['total'];

            $order->quantity = $quantity;
            $order->total_price = $discountedTotal;
            $order->coupon_code = $couponCode;
            $order->save();

            $message = 'Order updated successfully';
            if ($couponResult['coupon_applied']) {
                $message .= " with {$couponResult['discount_percent']}% discount applied";
            }

            return $this->successResponse(new LaundryOrderResource($order), $message);

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
