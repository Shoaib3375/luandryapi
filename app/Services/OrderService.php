<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use App\Contracts\OrderRepositoryInterface;
use App\DTOs\CreateOrderDTO;
use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Models\LaundryOrder;
use App\Models\OrderLog;
use App\Models\User;
use App\Repositories\ServiceRepository;
use App\Services\Validators\OrderValidator;
use Illuminate\Support\Facades\DB;

readonly class OrderService
{
    public function __construct(
        private OrderRepositoryInterface     $orderRepository,
        private ServiceRepository            $serviceRepository,
        private PriceCalculationService      $priceService,
        private CouponService                $couponService,
        private NotificationServiceInterface $notificationService,
        private OrderValidator               $validator,
    ) {}

    /**
     * @throws \Throwable
     */
    public function createOrder(array $data, int $userId): LaundryOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $totalPrice = 0;
            $orderItems = [];

            foreach ($data['services'] as $serviceData) {
                $service = $this->serviceRepository->findById($serviceData['service_id']);
                $itemPrice = $this->priceService->calculateBasePrice($service, (float)$serviceData['quantity']);
                $totalPrice += $itemPrice;
                
                $orderItems[] = [
                    'service_id' => $serviceData['service_id'],
                    'quantity' => $serviceData['quantity'],
                    'unit_price' => $service->price,
                    'total_price' => $itemPrice,
                ];
            }

            $couponResult = $this->couponService->calculateDiscount($totalPrice, $data['coupon_code'] ?? null);

            $order = LaundryOrder::create([
                'user_id' => $userId,
                'total_price' => $couponResult->total,
                'status' => 'Pending',
                'note' => $data['note'] ?? null,
                'coupon_code' => $data['coupon_code'] ?? null,
                'delivery_address_id' => $data['delivery_address_id'] ?? null,
            ]);

            foreach ($orderItems as $item) {
                $order->orderItems()->create($item);
            }

            return $order->fresh()->load('orderItems.service');
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

    /**
     * @throws \Throwable
     */
    public function cancelOrder(int $orderId, User $user): LaundryOrder
    {
        return DB::transaction(function () use ($orderId, $user) {
            $order = $this->orderRepository->findById($orderId);

            if (!$order) {
                throw OrderException::unauthorized();
            }

            $this->validator->validateOwnership($order, $user);
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

    /**
     * @throws \Throwable
     */
    public function updateOrder(int $orderId, array $data, User $user): LaundryOrder
    {
        return DB::transaction(function () use ($orderId, $data, $user) {
            $order = $this->orderRepository->findById($orderId);

            if (!$order) {
                throw OrderException::unauthorized();
            }

            $this->validator->validateOwnership($order, $user);
            $this->validator->validateUpdate($order);

            // Calculate total price from all order items
            $totalPrice = 0;
            foreach ($order->orderItems as $item) {
                if ($item->service_id === $order->orderItems->first()->service_id) {
                    $totalPrice += $this->priceService->calculateBasePrice($item->service, $data['quantity']);
                    break;
                }
            }
            $basePrice = $totalPrice ?: $this->priceService->calculateBasePrice($order->orderItems->first()->service, $data['quantity']);
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

    /**
     * @throws \Throwable
     */
    public function createGuestOrder(array $data): LaundryOrder
    {
        return DB::transaction(function () use ($data) {
            $totalPrice = 0;
            $orderItems = [];

            foreach ($data['services'] as $serviceData) {
                $service = $this->serviceRepository->findById($serviceData['service_id']);
                $itemPrice = $this->priceService->calculateBasePrice($service, (float)$serviceData['quantity']);
                $totalPrice += $itemPrice;
                
                $orderItems[] = [
                    'service_id' => $serviceData['service_id'],
                    'quantity' => $serviceData['quantity'],
                    'unit_price' => $service->price,
                    'total_price' => $itemPrice,
                ];
            }

            $order = LaundryOrder::create([
                'total_price' => $totalPrice,
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'guest_phone' => $data['guest_phone'],
                'guest_address' => $data['guest_address'],
                'note' => $data['note'] ?? null,
                'status' => OrderStatus::PENDING->value,
            ]);

            foreach ($orderItems as $item) {
                $order->orderItems()->create($item);
            }

            return $order->fresh()->load('orderItems.service');
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
