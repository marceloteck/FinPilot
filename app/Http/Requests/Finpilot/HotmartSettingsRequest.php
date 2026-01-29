<?php

namespace App\Http\Requests\Finpilot;

use Illuminate\Foundation\Http\FormRequest;

class HotmartSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_enabled' => ['nullable', 'boolean'],
            'product_id' => ['nullable', 'string', 'max:255'],
            'basic_key' => ['nullable', 'string', 'max:255'],
            'basic_secret' => ['nullable', 'string', 'max:255'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
            'webhook_url' => ['nullable', 'url', 'max:255'],
            'ai_enabled' => ['nullable', 'boolean'],
            'import_enabled' => ['nullable', 'boolean'],
            'reports_enabled' => ['nullable', 'boolean'],
            'max_users' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
