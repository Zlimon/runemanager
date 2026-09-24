<?php

namespace App\Http\Requests;

use App\Models\ResourcePack;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A viewer's resource pack choice: a pack id, null for "follow the instance
 * default", or {@see ResourcePack::GUEST_NO_PACK} for no resource pack.
 */
class UpdateResourcePackPreferenceRequest extends FormRequest
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
            'resource_pack_id' => $this->input('resource_pack_id') === ResourcePack::GUEST_NO_PACK
                ? ['string']
                : ['nullable', 'integer', 'exists:resource_packs,id'],
        ];
    }
}
