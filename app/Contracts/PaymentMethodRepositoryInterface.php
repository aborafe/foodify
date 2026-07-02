<?php

namespace App\Contracts;

use App\DTOs\PaymentMethod\StorePaymentMethodData;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface PaymentMethodRepositoryInterface
{
    /**
     * @return Collection<int, PaymentMethod>
     */
    public function listForUser(User $user): Collection;

    public function belongsToUser(int $paymentMethodId, User $user): bool;

    public function unsetDefaults(User $user): int;

    public function createForUser(User $user, StorePaymentMethodData $data): PaymentMethod;

    public function delete(PaymentMethod $paymentMethod): void;
}
