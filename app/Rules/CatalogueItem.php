<?php

namespace App\Rules;

use App\Models\Item;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/** An OSRS item id that exists in the static item catalogue. */
class CatalogueItem implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Item::forPicker((int) $value) === null) {
            $fail('That item does not exist.');
        }
    }
}
