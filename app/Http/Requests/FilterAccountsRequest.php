<?php

namespace App\Http\Requests;

use App\Enums\AccountTypesEnum;
use App\Rules\AccountUsernameRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterAccountsRequest extends FormRequest
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
            'username' => ['nullable', 'string', new AccountUsernameRule],
            'account_types' => ['nullable', 'array'],
            'account_types.*' => ['string', Rule::in(array_values(AccountTypesEnum::returnAllAccountTypes()))],
            'per_page' => ['nullable', 'integer', 'min:4', 'max:64'],
        ];
    }
}
