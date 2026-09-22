<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** A viewer's light/dark preference. */
class UpdateDarkModeRequest extends FormRequest
{
    /** Open to guests too: they get a cookie instead of a saved preference. */
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
            'dark_mode' => ['required', 'boolean'],
        ];
    }
}
