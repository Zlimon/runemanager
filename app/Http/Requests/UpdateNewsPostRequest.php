<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * An edit to an existing article. Identical to creating one, plus a flag for
 * clearing the header image back to the default — the same `remove_*` shape
 * {@see UpdateBrandingRequest} uses, and for the same reason: a multipart form
 * cannot express "this file field is now empty" any other way.
 */
class UpdateNewsPostRequest extends StoreNewsPostRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'remove_image' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function articleAttributes(): array
    {
        return array_merge(parent::articleAttributes(), [
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }
}
