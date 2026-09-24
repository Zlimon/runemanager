<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** An equipment snapshot pushed by the RuneLite plugin, indexed by KitType slot. */
class UpdateEquipmentRequest extends FormRequest
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
            'equipment' => ['required', 'array'],
            'equipment.*' => ['required', 'array', 'min:1'],
            'equipment.*.0' => ['required', 'integer'],
        ];
    }
}
