<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function listForUser(User $user): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForUser(User $user, array $attributes): Order;

    public function orderNumberExists(string $orderNumber): bool;
}
