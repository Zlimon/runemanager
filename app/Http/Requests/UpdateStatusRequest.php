<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** The current in-game activity pushed by the RuneLite plugin. */
class UpdateStatusRequest extends FormRequest
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
            'activity' => ['nullable', 'string', 'max:60'],
            'activity_icon' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:60'],
            'world' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
