<?php

namespace App\Http\Requests\Finpilot;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmAiPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ai_plan_id' => ['required', 'exists:ai_plans,id'],
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:ai_plan_items,id'],
            'items.*.confirmed_amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
