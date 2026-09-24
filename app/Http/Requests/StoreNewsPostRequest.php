<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A new article. Sent as multipart, because the optional header image rides
 * along with the fields.
 */
class StoreNewsPostRequest extends FormRequest
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
            'title' => ['required', 'string', 'min:2', 'max:160'],
            'excerpt' => ['required', 'string', 'min:2', 'max:300'],
            // Sanitised by SanitizeArticleHtml before storage; the cap is a
            // sanity bound on the editor's output, not a security control.
            'body' => ['required', 'string', 'max:60000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'published' => ['nullable', 'boolean'],
            // Only meaningful alongside `published`; a future date schedules the
            // post rather than showing it immediately.
            'published_at' => ['nullable', 'date'],
            'announce' => ['nullable', 'boolean'],
        ];
    }

    /**
     * The article fields as the save service wants them.
     *
     * The two flags are read with `boolean()` rather than taken from
     * `validated()` for two reasons. An unticked checkbox reaches a multipart
     * request as the string "0", which `validated()` hands on unchanged, and an
     * untouched one is absent entirely — so the service would be comparing
     * against a string or a missing key. Normalising here lets it test the flags
     * strictly, which is what keeps a draft from being announced.
     *
     * @return array<string, mixed>
     */
    public function articleAttributes(): array
    {
        return array_merge($this->validated(), [
            'published' => $this->boolean('published'),
            'announce' => $this->boolean('announce'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'excerpt.required' => 'Write a short summary for the listings.',
            'body.required' => 'The article cannot be empty.',
        ];
    }
}
