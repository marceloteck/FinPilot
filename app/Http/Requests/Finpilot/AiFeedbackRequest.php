<?php

namespace App\Http\Requests\Finpilot;

use Illuminate\Foundation\Http\FormRequest;

class AiFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:change_strategy,dismiss_suggestion,adjust_amount,confirm_plan'],
            'payload' => ['nullable', 'array'],
        ];
    }
}
