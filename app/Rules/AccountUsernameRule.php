<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AccountUsernameRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Trim whitespace from the beginning and end
        $username = trim($value);

        // Check length: 1 to 12 characters
        $length = mb_strlen($username);
        if ($length < 1 || $length > 12) {
            $fail('The :attribute must be between 1 and 12 characters.');
            return;
        }

        // Check for allowed characters: letters, numbers, and spaces
        if (!preg_match('/^[a-zA-Z0-9 ]+$/', $username)) {
            $fail('The :attribute may only contain letters, numbers, and spaces.');
            return;
        }

        // Check for consecutive spaces
        if (preg_match('/ {2,}/', $username)) {
            $fail('The :attribute may not contain consecutive spaces.');
            return;
        }

        // Check for leading or trailing spaces
        if (preg_match('/^ | $/', $value)) {
            $fail('The :attribute may not start or end with a space.');
            return;
        }

        // Check for inappropriate or offensive content
        $offensiveWords = [
            'badword1',
            'badword2',
            'badword3',
            'Jenny',
            // Add more words as needed
        ];

        $usernameLower = strtolower($username);
        foreach ($offensiveWords as $word) {
            if (strpos($usernameLower, strtolower($word)) !== false) {
                $fail('The :attribute contains inappropriate language.');
                return;
            }
        }
    }
}
