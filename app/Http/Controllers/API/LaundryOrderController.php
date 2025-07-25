<?php

namespace App\Http\Controllers\API;

use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Http\Resources\LaundryOrderResource;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class LaundryOrderController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderRepository $orderRepository,
    ) {}

    public function index(): JsonResponse
    {
        $orders = $this->orderRepository->getOrdersForUser(Auth::user());
        
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

            $order = $this->orderService->createOrder($validated, auth()->id());

            return $this->successResponse(
                new LaundryOrderResource($order),
                'Order placed successfully',
                201
            );

        } catch (Throwable $e) {
            return $this->exceptionResponse($e, 'Failed to place order.');
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:' . implode(',', OrderStatus::values()),
            ]);

            $status = OrderStatus::from($validated['status']);
            $order = $this->orderService->updateOrderStatus($id, $status, auth()->user());

            return $this->successResponse(
                new LaundryOrderResource($order),
                'Order status updated successfully'
            );

        } catch (OrderException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Throwable $e) {
            return $this->exceptionResponse($e, 'Failed to update order status.');
        }
    }

    public function filterByStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', OrderStatus::values()),
        ]);

        $status = OrderStatus::from($validated['status']);
        $orders = $this->orderRepository->getOrdersByStatus($status);

        return $this->successResponse(
            LaundryOrderResource::collection($orders),
            'Filtered orders by status'
        );
    }

    public function cancelOrder($id): JsonResponse
    {
        try {
            $order = $this->orderService->cancelOrder($id, auth()->user());

            return $this->successResponse(
                new LaundryOrderResource($order),
                'Order cancelled successfully.'
            );

        } catch (OrderException $e) {
            return $this->errorResponse($e->getMessage(), 400);
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

            $order = $this->orderService->updateOrder($id, $validated, auth()->user());

            return $this->successResponse(
                new LaundryOrderResource($order),
                'Order updated successfully'
            );

        } catch (OrderException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Order not found or unauthorized.', 404);
        } catch (Throwable $e) {
            return $this->exceptionResponse($e, 'Failed to update order.');
        }
    }

    public function userOrders(Request $request): JsonResponse
    {
        $orders = $this->orderRepository->getOrdersForUser(auth()->user());
        
        return $this->successResponse(
            LaundryOrderResource::collection($orders),
            'User orders retrieved successfully'
        );
    }
}