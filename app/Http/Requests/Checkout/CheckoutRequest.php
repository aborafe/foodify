<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_methods,id'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'estimated_delivery_time' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
