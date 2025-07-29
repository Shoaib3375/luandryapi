<?php

namespace App\Repositories;

use App\Contracts\OrderRepositoryInterface;
use App\DTOs\CreateOrderDTO;
use App\Enums\OrderStatus;
use App\Models\LaundryOrder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
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
            'delivery_address_id' => $dto->deliveryAddressId,
            'status' => OrderStatus::PENDING->value,
            'payment_status' => 'Unpaid',
        ]);
    }

    public function findById(int $id): ?LaundryOrder
    {
        return LaundryOrder::with(['service', 'user', 'deliveryAddress'])->find($id);
    }

    public function findByIdForUser(int $id, int $userId): ?LaundryOrder
    {
        return LaundryOrder::with(['service', 'user', 'deliveryAddress'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function getOrdersForUser(User $user, array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 10;
        $search = $params['search'] ?? null;
        $status = $params['status'] ?? null;
        
        $query = $user->is_admin 
            ? LaundryOrder::with(['service', 'user', 'deliveryAddress'])
            : LaundryOrder::with('service')->where('user_id', $user->id);

        if ($search) {
            $query->where(function($q) use ($search, $user) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('service', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
                if ($user->is_admin) {
                    $q->orWhereHas('user', function($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getOrdersByStatus(OrderStatus $status, int $perPage = 10): LengthAwarePaginator
    {
        return LaundryOrder::with(['user', 'service', 'deliveryAddress'])
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