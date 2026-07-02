<?php

namespace App\Repositories;

use App\Contracts\PaymentMethodRepositoryInterface;
use App\DTOs\PaymentMethod\StorePaymentMethodData;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EloquentPaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    public function listForUser(User $user): Collection
    {
        return $user->paymentMethods()->latest()->get();
    }

    public function belongsToUser(int $paymentMethodId, User $user): bool
    {
        return PaymentMethod::query()
            ->where('id', $paymentMethodId)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function unsetDefaults(User $user): int
    {
        return $user->paymentMethods()->update(['is_default' => false]);
    }

    public function createForUser(User $user, StorePaymentMethodData $data): PaymentMethod
    {
        return $user->paymentMethods()->create($data->toArray());
    }

    public function delete(PaymentMethod $paymentMethod): void
    {
        $paymentMethod->delete();
    }
}
