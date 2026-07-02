<?php

namespace App\Services;

use App\Contracts\PaymentMethodRepositoryInterface;
use App\DTOs\PaymentMethod\StorePaymentMethodData;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PaymentMethodService
{
    public function __construct(private readonly PaymentMethodRepositoryInterface $paymentMethods) {}

    public function list(User $user): Collection
    {
        return $this->paymentMethods->listForUser($user);
    }

    public function create(User $user, StorePaymentMethodData $data): PaymentMethod
    {
        if ($data->isDefault) {
            $this->paymentMethods->unsetDefaults($user);
        }

        return $this->paymentMethods->createForUser($user, $data);
    }

    public function delete(PaymentMethod $paymentMethod): void
    {
        $this->paymentMethods->delete($paymentMethod);
    }
}
