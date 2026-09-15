<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class Translatable implements ValidationRule
{
    /**
     * Create a new instance of the Translatable validation rule.
     *
     * @param  int|null  $minLength  The minimum length of each translation.
     * @param  int|null  $maxLength  The maximum length of each translation.
     * @param  int|null  $minItems  The minimum number of translations required.
     * @param  int|null  $maxItems  The maximum number of translations allowed.
     */
    public function __construct(
        private readonly ?int $minLength = null,
        private readonly ?int $maxLength = null,
        private readonly ?int $minItems = null,
        private readonly ?int $maxItems = null,
    ) {}

    /**
     * Apply the validation rule to the given attribute and value.
     *
     * @param  string  $attribute  The name of the attribute being validated.
     * @param  mixed  $value  The value of the attribute.
     * @param  Closure  $fail  A callback to call when validation fails.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value)) {
            $this->validateLength($attribute, $value, $fail);

            return;
        }

        if (! is_array($value)) {
            $fail(__('validation.translatable.type', ['attribute' => $attribute]));

            return;
        }

        $this->validateItemCount($attribute, $value, $fail);

        $supportedLocales = config('boxsys.locales.supported', []);

        foreach ($value as $locale => $translation) {
            if (! is_string($locale) || ! in_array($locale, $supportedLocales, true)) {
                $fail(
                    __('validation.translatable.unsupported', ['attribute' => $attribute, 'locale' => (string) $locale])
                );

                return;
            }

            if (! is_string($translation)) {
                $fail(
                    __('validation.translatable.translation_type', ['attribute' => $attribute])
                );

                return;
            }

            $this->validateLength($attribute, $translation, $fail);
        }
    }

    /**
     * Validate the length of a translation.
     *
     * @param  string  $attribute  The name of the attribute being validated.
     * @param  string  $value  The translation value.
     * @param  Closure  $fail  A callback to call when validation fails.
     */
    private function validateLength(string $attribute, string $value, Closure $fail): void
    {
        $length = mb_strlen($value);

        if ($this->minLength !== null && $length < $this->minLength) {
            $fail(
                __('validation.translatable.string.min', ['attribute' => $attribute, 'min' => $this->minLength])
            );

            return;
        }

        if ($this->maxLength !== null && $length > $this->maxLength) {
            $fail(
                __('validation.translatable.string.max', ['attribute' => $attribute, 'max' => $this->maxLength])
            );
        }
    }

    /**
     * Validate the number of translation items.
     *
     * @param  string  $attribute  The name of the attribute being validated.
     * @param  array<string, string>  $value  The translation array.
     * @param  Closure  $fail  A callback to call when validation fails.
     */
    private function validateItemCount(string $attribute, array $value, Closure $fail): void
    {
        $count = count($value);

        if ($this->minItems !== null && $count < $this->minItems) {
            $fail(
                __('validation.translatable.array.min', ['attribute' => $attribute, 'min' => $this->minItems])
            );

            return;
        }

        if ($this->maxItems !== null && $count > $this->maxItems) {
            $fail(
                __('validation.translatable.array.max', ['attribute' => $attribute, 'max' => $this->maxItems])
            );
        }
    }
}
