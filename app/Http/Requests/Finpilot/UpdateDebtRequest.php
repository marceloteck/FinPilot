<?php

namespace App\Http\Requests\Finpilot;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'debt_type_id' => ['required', 'exists:debt_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'creditor' => ['nullable', 'string', 'max:255'],
            'total_amount' => ['nullable', 'numeric'],
            'monthly_minimum' => ['required', 'numeric', 'min:0.01'],
            'interest_rate' => ['nullable', 'numeric'],
            'due_day' => ['required', 'integer', 'between:1,31'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:active,paid,renegotiating'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
