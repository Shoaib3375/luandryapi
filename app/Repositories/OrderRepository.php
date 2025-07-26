<?php

namespace App\Repositories;

use App\DTOs\CreateOrderDTO;
use App\Enums\OrderStatus;
use App\Models\LaundryOrder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    public function create(CreateOrderDTO $dto): LaundryOrder
    {
        return LaundryOrder::create([
            'user_id' => $dto->userId,
            'service_id' => $dto->serviceId,
            'quantity' => $dto->quantity,
            'total_price' => $dto->totalPrice,
            'note' => $dto->note,
            'coupon_code' => $dto->couponCode,
            'status' => OrderStatus::PENDING->value,
            'payment_status' => 'Unpaid',
        ]);
    }

    public function findById(int $id): ?LaundryOrder
    {
        return LaundryOrder::with(['service', 'user'])->find($id);
    }

    public function findByIdForUser(int $id, int $userId): ?LaundryOrder
    {
        return LaundryOrder::with(['service', 'user'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function getOrdersForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        if ($user->is_admin) {
            return LaundryOrder::with(['service', 'user'])
                ->latest()
                ->paginate($perPage);
        }

        return LaundryOrder::with('service')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function getOrdersByStatus(OrderStatus $status, int $perPage = 10): LengthAwarePaginator
    {
        return LaundryOrder::with(['user', 'service'])
            ->where('status', $status->value)
            ->latest()
            ->paginate($perPage);
    }

    public function updateStatus(LaundryOrder $order, OrderStatus $status): bool
    {
        return $order->update(['status' => $status->value]);
    }

    public function updateOrder(LaundryOrder $order, array $data): bool
    {
        return $order->update($data);
    }
}