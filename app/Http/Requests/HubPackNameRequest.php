<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A community hub pack name: a `pack-*` branch of melkypie/resource-packs.
 * Shared by the member and admin install endpoints, the install status poll
 * and the hub details lookup.
 */
class HubPackNameRequest extends FormRequest
{
    /**
     * Auth and permissions are enforced by the route middleware.
     */
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
            'name' => ['required', 'string', 'max:100', 'regex:/^pack-[A-Za-z0-9_-]+$/'],
        ];
    }
}
