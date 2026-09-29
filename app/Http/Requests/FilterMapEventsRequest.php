<?php

namespace App\Http\Requests;

use App\Models\FeedEvent;
use App\Services\Map\EventMap;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The event map's selection: account ids and an optional event type. Ids the
 * viewer may not see (or that do not exist) are dropped by EventMap rather
 * than rejected, so a shared link degrades instead of failing.
 */
class FilterMapEventsRequest extends FormRequest
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
            'accounts' => ['nullable', 'array', 'max:'.EventMap::MAX_ACCOUNTS],
            'type' => ['nullable', 'string', Rule::in(FeedEvent::TYPES)],
        ];
    }
}
