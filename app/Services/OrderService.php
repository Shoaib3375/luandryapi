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

    /**
     * @throws \Throwable
     */
    public function createGuestOrder(array $data): LaundryOrder
    {
        return DB::transaction(function () use ($data) {
            $service = $this->serviceRepository->findById($data['service_id']);
            $totalPrice = $this->priceService->calculateBasePrice($service, $data['quantity']);

            return LaundryOrder::create([
                'service_id' => $data['service_id'],
                'quantity' => $data['quantity'],
                'total_price' => $totalPrice,
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'guest_phone' => $data['guest_phone'],
                'guest_address' => $data['guest_address'],
                'note' => $data['note'] ?? null,
                'status' => OrderStatus::PENDING->value,
            ]);
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
