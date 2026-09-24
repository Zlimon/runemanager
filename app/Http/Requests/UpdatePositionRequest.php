<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** The player's Live Map WorldPoint pushed by the RuneLite plugin. */
class UpdatePositionRequest extends FormRequest
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
            'x' => ['required', 'integer'],
            'y' => ['required', 'integer'],
            'plane' => ['required', 'integer', 'min:0', 'max:3'],
        ];
    }
}
