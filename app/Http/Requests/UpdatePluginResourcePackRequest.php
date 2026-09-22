<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The plugin's active pack name. Unlike {@see HubPackNameRequest} it has no
 * `pack-` prefix: the plugin reports the name RuneLite shows.
 */
class UpdatePluginResourcePackRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }
}
