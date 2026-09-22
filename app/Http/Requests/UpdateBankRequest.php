<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** A bank snapshot pushed by the RuneLite plugin. */
class UpdateBankRequest extends FormRequest
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
            'bank' => ['required', 'array', 'max:10'],            // tabs
            'bank.*' => ['required', 'array'],                    // items in a tab
            'bank.*.*' => ['required', 'array', 'size:2'],        // [itemId, quantity]
            'bank.*.*.*' => ['required', 'integer'],
        ];
    }
}
