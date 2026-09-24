<?php

namespace App\Http\Requests;

use App\Support\Roles;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** The full clan roster pushed by the RuneLite plugin. */
class StoreClanRosterRequest extends FormRequest
{
    /**
     * Seeding the roster needs the member-management permission (the clan
     * owner is the instance Owner). Checked here so it answers 403 before
     * the payload is validated.
     */
    public function authorize(): Response
    {
        return $this->user()->can(Roles::MANAGE_MEMBERS)
            ? Response::allow()
            : Response::deny('You do not have permission to seed the clan roster.');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clan_name' => ['nullable', 'string', 'max:60'],
            'members' => ['present', 'array'],
            'members.*.username' => ['required', 'string', 'max:13'],
            'members.*.rank' => ['nullable', 'integer', 'between:-128,127'],
            'members.*.title' => ['nullable', 'string', 'max:60'],
        ];
    }
}
