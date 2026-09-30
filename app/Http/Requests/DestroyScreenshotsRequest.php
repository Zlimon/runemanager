<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Delete some screenshots, keeping their feed events: the owner's own
 * (Settings → Screenshots) or, on the admin route, any account's
 * (Admin → Screenshots).
 */
class DestroyScreenshotsRequest extends FormRequest
{
    /** Both routes are permission-gated (`auth`, or `can:manage instance`); ownership is checked after validation. */
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
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'min:1'],
        ];
    }
}
