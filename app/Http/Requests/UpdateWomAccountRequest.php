<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * An account owner's Wise Old Man choices: whether it shows on /progress, and
 * whether it may be tracked (asked to update). Either may be sent alone.
 */
class UpdateWomAccountRequest extends FormRequest
{
    /** Owner-only, checked before validation so a stranger gets a 403 rather than a 422. */
    public function authorize(): Response
    {
        return $this->user()?->id === $this->route('account')?->user_id
            ? Response::allow()
            : Response::deny('Only the account owner can change its Wise Old Man settings.');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'show_on_progress' => ['required_without:tracking_opt_in', 'boolean'],
            'tracking_opt_in' => ['sometimes', 'boolean'],
        ];
    }
}
