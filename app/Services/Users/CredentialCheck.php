<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Email + password check shared by the website login (Fortify) and the
 * plugin's `/api/login`.
 *
 * An unknown email still pays for one hash check, against a throwaway hash
 * made with the configured cost, so the response time does not tell an
 * attacker which emails are registered.
 */
class CredentialCheck
{
    private static ?string $dummyHash = null;

    public function user(?string $email, ?string $password): ?User
    {
        $user = $email === null ? null : User::query()->where('email', $email)->first();

        $hash = $user?->password ?? (self::$dummyHash ??= Hash::make(Str::random(40)));

        if (! Hash::check((string) $password, $hash) || $user === null) {
            return null;
        }

        return $user;
    }
}
