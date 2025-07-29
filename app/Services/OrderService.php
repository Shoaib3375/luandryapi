<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use App\Contracts\OrderRepositoryInterface;
use App\DTOs\CreateOrderDTO;
use App\Enums\OrderStatus;
use App\Models\LaundryOrder;
use App\Models\OrderLog;
use App\Models\User;
use App\Repositories\ServiceRepository;
use App\Services\Validators\OrderValidator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ServiceRepository $serviceRepository,
        private readonly PriceCalculationService $priceService,
        private readonly CouponService $couponService,
        private readonly NotificationServiceInterface $notificationService,
        private readonly OrderValidator $validator,
    ) {}

    public function createOrder(array $data, int $userId): LaundryOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $service = $this->serviceRepository->findById($data['service_id']);
            
            $basePrice = $this->priceService->calculateBasePrice($service, $data['quantity']);
            $couponResult = $this->couponService->calculateDiscount($basePrice, $data['coupon_code'] ?? null);

            $dto = CreateOrderDTO::fromArray([
                ...$data,
                'total_price' => $couponResult->total,
            ], $userId);

            return $this->orderRepository->create($dto);
        });
    }

    public function updateOrderStatus(int $orderId, OrderStatus $status, User $admin): LaundryOrder
    {
        return DB::transaction(function () use ($orderId, $status, $admin) {
            $order = $this->orderRepository->findById($orderId);
            $this->validator->validateStatusUpdate($order, $status, $admin);
            
            $oldStatus = OrderStatus::from($order->status);
            if ($oldStatus === $status) {
                return $order;
            }

            $this->orderRepository->updateStatus($order, $status);
            $this->logStatusChange($order, $oldStatus, $status, $admin);
            $this->notificationService->notifyOrderStatusUpdate($order);

            return $order->fresh();
        });
    }

    public function cancelOrder(int $orderId, User $user): LaundryOrder
    {
        return DB::transaction(function () use ($orderId, $user) {
            $order = $user->is_admin 
                ? $this->orderRepository->findById($orderId)
                : $this->orderRepository->findByIdForUser($orderId, $user->id);

            if (!$order) {
                throw OrderException::unauthorized();
            }

            $this->validator->validateCancellation($order);

            $oldStatus = OrderStatus::from($order->status);
            $this->orderRepository->updateStatus($order, OrderStatus::CANCELLED);
            $this->logStatusChange($order, $oldStatus, OrderStatus::CANCELLED, $user);

            if ($user->is_admin && $order->user_id !== $user->id) {
                $this->notificationService->notifyOrderStatusUpdate($order);
            }

            return $order->fresh();
        });
    }

    public function updateOrder(int $orderId, array $data, User $user): LaundryOrder
    {
        return DB::transaction(function () use ($orderId, $data, $user) {
            $order = $user->is_admin 
                ? $this->orderRepository->findById($orderId)
                : $this->orderRepository->findByIdForUser($orderId, $user->id);

            if (!$order) {
                throw OrderException::unauthorized();
            }

            $this->validator->validateUpdate($order);

            $basePrice = $this->priceService->calculateBasePrice($order->service, $data['quantity']);
            $couponCode = $data['coupon_code'] ?? $order->coupon_code;
            $couponResult = $this->couponService->calculateDiscount($basePrice, $couponCode);

            $updateData = [
                'quantity' => $data['quantity'],
                'total_price' => $couponResult->total,
                'coupon_code' => $couponCode,
            ];

            $this->orderRepository->updateOrder($order, $updateData);

            return $order->fresh();
        });
    }

    private function logStatusChange(LaundryOrder $order, OrderStatus $oldStatus, OrderStatus $newStatus, User $user): void
    {
        OrderLog::create([
            'order_id' => $order->id,
            'admin_id' => $user->id,
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
        ]);
    }
}