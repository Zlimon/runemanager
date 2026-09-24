<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** The shared Group Ironman bank pushed by the RuneLite plugin. */
class UpdateGroupBankRequest extends FormRequest
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
            'group_bank' => ['present', 'array'],
            'group_bank.*' => ['array', 'size:2'],
            'group_bank.*.*' => ['integer'],
        ];
    }
}
