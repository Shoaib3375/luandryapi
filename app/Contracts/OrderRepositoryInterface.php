<?php

namespace App\Contracts;

use App\DTOs\CreateOrderDTO;
use App\Enums\OrderStatus;
use App\Models\LaundryOrder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function create(CreateOrderDTO $dto): LaundryOrder;
    public function findById(int $id): ?LaundryOrder;
    public function findByIdForUser(int $id, int $userId): ?LaundryOrder;
    public function getOrdersForUser(User $user, array $params = []): LengthAwarePaginator;
    public function getOrdersByStatus(OrderStatus $status, int $perPage = 10): LengthAwarePaginator;
    public function updateStatus(LaundryOrder $order, OrderStatus $status): bool;
    public function updateOrder(LaundryOrder $order, array $data): bool;
}