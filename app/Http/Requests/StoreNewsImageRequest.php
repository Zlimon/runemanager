<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** An image dropped into an article body from the editor's toolbar. */
class StoreNewsImageRequest extends FormRequest
{
    /** Access is enforced by the route's `can:manage news` middleware. */
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
            'image' => ['required', 'image', 'max:4096'],
        ];
    }
}
