<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
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
            'meal_id' => ['required', 'integer', 'exists:meals,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
