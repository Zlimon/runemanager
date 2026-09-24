<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Status-orb values pushed by the RuneLite plugin. */
class UpdateVitalsRequest extends FormRequest
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
            'hitpoints' => ['required', 'integer', 'min:0'],
            'hitpoints_max' => ['required', 'integer', 'min:1'],
            'prayer' => ['required', 'integer', 'min:0'],
            'prayer_max' => ['required', 'integer', 'min:1'],
            'run_energy' => ['required', 'integer', 'min:0', 'max:100'],
            'special_attack' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }
}
