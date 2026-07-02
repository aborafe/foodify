<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSavedReportRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'metric' => ['required', Rule::in(['sales', 'orders', 'customers', 'meals'])],
            'date_range' => ['required', 'string', 'max:255'],
            'export_format' => ['required', Rule::in(['pdf', 'csv', 'xlsx'])],
            'included_sections' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
        ];
    }
}
