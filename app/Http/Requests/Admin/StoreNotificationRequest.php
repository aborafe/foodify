<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('employee')?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'audience' => ['required', Rule::in(['all', 'single'])],
            'user_id' => ['nullable', 'required_if:audience,single', 'integer', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'type' => ['required', Rule::in(['order', 'health_tip', 'offer', 'system'])],
            'image' => ['nullable', 'url', 'max:255'],
            'is_read' => ['nullable', 'boolean'],
        ];
    }
}
