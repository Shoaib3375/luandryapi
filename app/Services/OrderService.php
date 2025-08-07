<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use App\Contracts\OrderRepositoryInterface;
use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Models\LaundryOrder;
use App\Models\OrderLog;
use App\Models\User;
use App\Repositories\ServiceRepository;
use App\Services\Validators\OrderValidator;
use Illuminate\Support\Facades\DB;
use Throwable;

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
     * @throws Throwable
     */
    public function createOrder(array $data, int $userId): LaundryOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            [$totalPrice, $orderItems] = $this->calculateOrderItems($data['services']);
            $couponResult = $this->couponService->calculateDiscount($totalPrice, $data['coupon_code'] ?? null);

            $order = LaundryOrder::create([
                'user_id' => $userId,
                'total_price' => $couponResult->total,
                'status' => 'Pending',
                'note' => $data['note'] ?? null,
                'coupon_code' => $data['coupon_code'] ?? null,
                'delivery_address_id' => $data['delivery_address_id'] ?? null,
            ]);

            $this->createOrderItems($order, $orderItems);
            return $order->fresh()->load('orderItems.service');
        });
    }

    /**
     * @throws Throwable
     */
    public function updateOrderStatus(int $orderId, OrderStatus $status, User $admin): LaundryOrder
    {
        return DB::transaction(function () use ($orderId, $status, $admin) {
            $order = $this->orderRepository->findById($orderId);
            $this->validator->validateStatusUpdate($order, $status, $admin);

            if (OrderStatus::from($order->status) === $status) {
                return $order;
            }

            $this->changeOrderStatus($order, $status, $admin);
            $this->notificationService->notifyOrderStatusUpdate($order);

            return $order->fresh();
        });
    }

    /**
     * @throws Throwable
     */
    public function cancelOrder(int $orderId, User $user): LaundryOrder
    {
        return DB::transaction(function () use ($orderId, $user) {
            $order = $this->findAndValidateOrder($orderId, $user);
            $this->validator->validateCancellation($order);

            $this->changeOrderStatus($order, OrderStatus::CANCELLED, $user);

            if ($user->is_admin && $order->user_id !== $user->id) {
                $this->notificationService->notifyOrderStatusUpdate($order);
            }

            return $order->fresh();
        });
    }

    /**
     * @throws Throwable
     */
    public function updateOrder(int $orderId, array $data, User $user): LaundryOrder
    {
        return DB::transaction(function () use ($orderId, $data, $user) {
            $order = $this->findAndValidateOrder($orderId, $user);
            $this->validator->validateUpdate($order);

            $basePrice = $this->priceService->calculateBasePrice($order->orderItems->first()->service, $data['quantity']);
            $couponCode = $data['coupon_code'] ?? $order->coupon_code;
            $couponResult = $this->couponService->calculateDiscount($basePrice, $couponCode);

            $this->orderRepository->updateOrder($order, [
                'quantity' => $data['quantity'],
                'total_price' => $couponResult->total,
                'coupon_code' => $couponCode,
            ]);

            return $order->fresh();
        });
    }

    /**
     * @throws Throwable
     */
    public function createGuestOrder(array $data): LaundryOrder
    {
        return DB::transaction(function () use ($data) {
            [$totalPrice, $orderItems] = $this->calculateOrderItems($data['services']);

            $order = LaundryOrder::create([
                'total_price' => $totalPrice,
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'guest_phone' => $data['guest_phone'],
                'guest_address' => $data['guest_address'],
                'note' => $data['note'] ?? null,
                'status' => OrderStatus::PENDING->value,
            ]);

            $this->createOrderItems($order, $orderItems);
            return $order->fresh()->load('orderItems.service');
        });
    }

    private function calculateOrderItems(array $services): array
    {
        $totalPrice = 0;
        $orderItems = [];

        foreach ($services as $serviceData) {
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

        return [$totalPrice, $orderItems];
    }

    private function createOrderItems(LaundryOrder $order, array $orderItems): void
    {
        foreach ($orderItems as $item) {
            $order->orderItems()->create($item);
        }
    }

    /**
     * @throws OrderException
     */
    private function findAndValidateOrder(int $orderId, User $user): LaundryOrder
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            throw OrderException::unauthorized();
        }

        $this->validator->validateOwnership($order, $user);
        return $order;
    }

    private function changeOrderStatus(LaundryOrder $order, OrderStatus $newStatus, User $user): void
    {
        $oldStatus = OrderStatus::from($order->status);
        $this->orderRepository->updateStatus($order, $newStatus);
        $this->logStatusChange($order, $oldStatus, $newStatus, $user);
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
