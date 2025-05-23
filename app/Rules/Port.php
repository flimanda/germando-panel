<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Port implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_numeric($value)) {
            $fail('Das :attribute muss nummerisch sein.');
        }

        $value = intval($value);
        if (floatval($value) !== (float) $value) {
            $fail('Das :attribute muss eine ganze Zahl sein.');
        }

        if ($value < 0) {
            $fail('Das :attribute muss größer oder gleich 0 sein.');
        }

        if ($value > 65535) {
            $fail('Das :attribute muss kleiner oder gleich 65535 sein.');
        }
    }
}
