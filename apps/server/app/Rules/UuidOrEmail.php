<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class UuidOrEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! str()->isUuid($value) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $fail(
                __('validation.uuid_or_email', compact('attribute'))
            );
        }
    }
}
