<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user('employee')?->role, ['admin', 'cashier'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'payment_method_id' => ['nullable', 'integer', Rule::exists('payment_methods', 'id')],
            'status' => ['required', Rule::in(['pending', 'confirmed', 'preparing', 'on_the_way', 'delivered', 'cancelled'])],
            'payment_status' => ['required', Rule::in(['pending', 'paid', 'failed'])],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'manual_adjustment' => ['nullable', 'numeric', 'min:-999999.99', 'max:999999.99'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'estimated_delivery_time' => ['nullable', 'integer', 'min:1', 'max:300'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.meal_id' => ['required', 'integer', Rule::exists('meals', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * Get the after validation callables for the request.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->user('employee')?->role === 'admin') {
                    return;
                }

                if (in_array($this->input('status'), ['on_the_way', 'cancelled'], true)) {
                    $validator->errors()->add('status', 'Only admins can set orders as on the way or cancelled.');
                }
            },
        ];
    }
}
