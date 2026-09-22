<?php

namespace App\Http\Requests;

use App\Support\Instance;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** A roster placeholder added by hand from the admin member list. */
class StoreMemberRequest extends FormRequest
{
    /**
     * The route's permission middleware admits the user; the roster only exists
     * in clan or group mode, which is checked here so it answers 403 before the
     * username is validated.
     */
    public function authorize(): Response
    {
        return Instance::requiresRosterClaim()
            ? Response::allow()
            : Response::deny('Members are only managed in clan or group mode.');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:12', 'regex:/^[A-Za-z0-9 _-]+$/', 'unique:accounts,username'],
        ];
    }
}
