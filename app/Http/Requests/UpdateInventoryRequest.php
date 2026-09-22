<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** An inventory snapshot pushed by the RuneLite plugin. */
class UpdateInventoryRequest extends FormRequest
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
            'inventory' => ['required', 'array', 'max:28'],
            'inventory.*' => ['required', 'array', 'max:2'],
            'inventory.*.0' => ['required', 'integer'],
            'inventory.*.1' => ['required', 'integer'],
        ];
    }
}
