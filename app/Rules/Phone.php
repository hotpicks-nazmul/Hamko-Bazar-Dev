<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Phone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Accepts:
     *  - BD local: 01XXXXXXXXX (11 digits, starts with 01)
     *  - BD with 880: 8801XXXXXXXXX (12 digits, no + — mobile register format)
     *  - International: +8801XXXXXXXXX (BD with country code)
     *  - International: +[country code][number] (any valid E.164 format)
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a valid phone number.');
            return;
        }

        $value = trim($value);

        // BD local format: 01XXXXXXXXX (exactly 11 digits starting with 01)
        if (preg_match('/^01[0-9]{9}$/', $value)) {
            return;
        }

        // BD with 880 prefix (no +): 8801XXXXXXXXX (12 digits)
        // Used by mobile register flow which prepends country code without +
        if (preg_match('/^8801[0-9]{9}$/', $value)) {
            return;
        }

        // International format: + followed by country code and number
        // E.164: +[1-9][0-9]{7,14} = 8-15 digits total including +
        if (preg_match('/^\+[1-9][0-9]{7,14}$/', $value)) {
            return;
        }

        $fail('The :attribute must be a valid phone number (BD: 01XXXXXXXXX or international: +XXXXXXXXX).');
    }
}
