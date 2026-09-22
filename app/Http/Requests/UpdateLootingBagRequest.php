<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** A looting bag snapshot pushed by the RuneLite plugin. */
class UpdateLootingBagRequest extends FormRequest
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
            'looting_bag' => ['required', 'array', 'max:28'],
            'looting_bag.*' => ['required', 'array', 'max:2'],
            'looting_bag.*.0' => ['required', 'integer'],
            'looting_bag.*.1' => ['required', 'integer'],
        ];
    }
}
