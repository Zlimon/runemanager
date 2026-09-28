<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * An account owner's privacy choices (Settings → Privacy). Each may be sent
 * alone; the Wise Old Man ones are ignored while the integration is off.
 */
class UpdateAccountPrivacyRequest extends FormRequest
{
    /** Owner-only, checked before validation so a stranger gets a 403 rather than a 422. */
    public function authorize(): Response
    {
        return $this->user()?->id === $this->route('account')?->user_id
            ? Response::allow()
            : Response::deny('Only the account owner can change its privacy settings.');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'show_events_on_map' => ['sometimes', 'boolean'],
            'hide_new_screenshots' => ['sometimes', 'boolean'],
            'show_on_progress' => ['sometimes', 'boolean'],
            'tracking_opt_in' => ['sometimes', 'boolean'],
        ];
    }
}
