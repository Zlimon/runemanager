<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Outbound integrations: the instance webhook and Wise Old Man. */
class UpdateIntegrationsRequest extends FormRequest
{
    /** Access is enforced by the route middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'webhook_url' => ['nullable', 'url', 'max:2000'],
            'wom_enabled' => ['nullable', 'boolean'],
            'wom_active_within_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'wom_progress_public' => ['nullable', 'boolean'],
        ];
    }
}
