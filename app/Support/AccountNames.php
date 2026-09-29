<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * How account names appear on pages logged-out visitors can see. With the
 * owner's "Hide account names" setting on, guests only get masked names.
 */
class AccountNames
{
    /** Whether names shown to this viewer must be masked. */
    public static function maskedFor(?User $viewer): bool
    {
        return $viewer === null && Instance::publicAnonymizeAccounts();
    }

    /** Keep the first letter, mask the rest — e.g. "Zezima" → "Z•••••". */
    public static function mask(string $username): string
    {
        if ($username === '') {
            return 'Anonymous';
        }

        return Str::substr($username, 0, 1).str_repeat('•', max(2, Str::length($username) - 1));
    }
}
