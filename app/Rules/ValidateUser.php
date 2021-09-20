<?php

namespace App\Rules;

use App\User;
use Illuminate\Contracts\Validation\Rule;

class ValidateUser implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = User::whereName($value)->orWhere('id', $value)->first();

        return isset($user);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The user must be a valid, existing user.';
    }
}
