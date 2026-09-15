<?php

declare(strict_types=1);

use App\Rules\Translatable;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    config(['boxsys.locales.supported' => ['en', 'pt', 'fr']]);
});

describe('string', function () {
    it('passes when the value is a string', function () {
        $validator = Validator::make(
            ['field' => 'Some value'],
            ['field' => [new Translatable()]],
        );

        expect($validator->passes())->toBeTrue();
    });
});

describe('string length', function () {
    it('passes when the string length is within minLength and maxLength', function () {
        $validator = Validator::make(
            ['field' => 'Hello'],
            ['field' => [new Translatable(minLength: 3, maxLength: 10)]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('passes when the string length equals minLength', function () {
        $validator = Validator::make(
            ['field' => 'abc'],
            ['field' => [new Translatable(minLength: 3, maxLength: 10)]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('passes when the string length equals maxLength', function () {
        $validator = Validator::make(
            ['field' => 'abcdefghij'],
            ['field' => [new Translatable(minLength: 3, maxLength: 10)]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('fails when the string is shorter than minLength', function () {
        $validator = Validator::make(
            ['field' => 'ab'],
            ['field' => [new Translatable(minLength: 3, maxLength: 10)]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.string.min', ['attribute' => 'field', 'min' => 3]));
    });

    it('fails when the string is longer than maxLength', function () {
        $validator = Validator::make(
            ['field' => 'this string is too long'],
            ['field' => [new Translatable(minLength: 3, maxLength: 10)]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.string.max', ['attribute' => 'field', 'max' => 10]));
    });

    it('validates maxLength when only maxLength is provided', function () {
        $validator = Validator::make(
            ['field' => 'this string is too long'],
            ['field' => [new Translatable(maxLength: 10)]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.string.max', ['attribute' => 'field', 'max' => 10]));
    });

    it('ignores length constraints when minLength/maxLength are not provided', function () {
        $validator = Validator::make(
            ['field' => 'a'],
            ['field' => [new Translatable()]],
        );

        expect($validator->passes())->toBeTrue();
    });
});

describe('array', function () {
    it('passes when the value is an empty array', function () {
        $validator = Validator::make(
            ['field' => []],
            ['field' => [new Translatable()]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('passes when the array contains a supported locale', function () {
        $validator = Validator::make(
            ['field' => ['fr' => 'Valeur']],
            ['field' => [new Translatable()]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('passes when the array contains all supported locales', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'Value', 'pt' => 'Valor']],
            ['field' => [new Translatable()]],
        );

        expect($validator->passes())->toBeTrue();
    });
});

describe('arrays length', function () {
    it('passes when the number of translations is within minItems and maxItems', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'Value', 'pt' => 'Valor']],
            ['field' => [new Translatable(minItems: 1, maxItems: 3)]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('passes when the number of translations equals minItems', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'Value']],
            ['field' => [new Translatable(minItems: 1, maxItems: 3)]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('passes when the number of translations equals maxItems', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'Value', 'pt' => 'Valor', 'fr' => 'Valeur']],
            ['field' => [new Translatable(minItems: 1, maxItems: 3)]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('fails when there are fewer translations than minItems', function () {
        $validator = Validator::make(
            ['field' => []],
            ['field' => [new Translatable(minItems: 1, maxItems: 3)]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.array.min', ['attribute' => 'field', 'min' => 1]));
    });

    it('fails when there are more translations than maxItems', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'Value', 'pt' => 'Valor', 'fr' => 'Valeur']],
            ['field' => [new Translatable(minItems: 1, maxItems: 2)]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.array.max', ['attribute' => 'field', 'max' => 2]));
    });

    it('validates maxItems when only maxItems is provided', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'Value', 'pt' => 'Valor', 'fr' => 'Valeur']],
            ['field' => [new Translatable(maxItems: 2)]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.array.max', ['attribute' => 'field', 'max' => 2]));
    });

    it('ignores item count constraints when minItems/maxItems are not provided', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'Value']],
            ['field' => [new Translatable()]],
        );

        expect($validator->passes())->toBeTrue();
    });
});

describe('locale', function () {
    it('fails when the array contains an unsupported locale', function () {
        $validator = Validator::make(
            ['field' => ['de' => 'Wert']],
            ['field' => [new Translatable()]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.unsupported', ['attribute' => 'field', 'locale' => 'de']));
    });

    it('fails when the array mixes supported and unsupported locales', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'Value', 'de' => 'Wert']],
            ['field' => [new Translatable()]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.unsupported', ['attribute' => 'field', 'locale' => 'de']));
    });

    it('fails when the array key is not a string', function () {
        $validator = Validator::make(
            ['field' => [0 => 'Value', 1 => 'Valor']],
            ['field' => [new Translatable()]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.unsupported', ['attribute' => 'field', 'locale' => '0']));
    });

    it('respects the configured supported locales', function () {
        config(['boxsys.locales.supported' => ['de']]);

        $validator = Validator::make(
            ['field' => ['de' => 'Wert']],
            ['field' => [new Translatable()]],
        );

        expect($validator->passes())->toBeTrue();
    });
});

describe('translation', function () {
    it('fails when translation is not a string', function () {
        $validator = Validator::make(
            ['field' => ['en' => 123]],
            ['field' => [new Translatable()]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.translation_type', ['attribute' => 'field']));
    });
});

describe('translation length', function () {

    it('passes when every translation length is within minLength and maxLength', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'Hello', 'pt' => 'Olá!!']],
            ['field' => [new Translatable(minLength: 3, maxLength: 10)]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('passes when a translation length equals minLength', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'abc']],
            ['field' => [new Translatable(minLength: 3, maxLength: 10)]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('passes when a translation length equals maxLength', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'abcdefghij']],
            ['field' => [new Translatable(minLength: 3, maxLength: 10)]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('fails when a translation is shorter than minLength', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'ab']],
            ['field' => [new Translatable(minLength: 3, maxLength: 10)]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.string.min', ['attribute' => 'field', 'min' => 3]));
    });

    it('fails when a translation is longer than maxLength', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'this translation is too long']],
            ['field' => [new Translatable(minLength: 3, maxLength: 10)]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.string.max', ['attribute' => 'field', 'max' => 10]));
    });

    it('fails when multiple translations exceed maxLength', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'this translation is too long', 'pt' => 'esta tradução também é longa']],
            ['field' => [new Translatable(minLength: 3, maxLength: 10)]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->get('field'))->toHaveCount(1);
    });

    it('ignores translation length constraints when minLength/maxLength are not provided', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'a']],
            ['field' => [new Translatable()]],
        );

        expect($validator->passes())->toBeTrue();
    });
});

describe('combined constraints', function () {
    it('passes when item count and translation lengths are within bounds', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'Value', 'pt' => 'Valor']],
            ['field' => [new Translatable(minLength: 3, maxLength: 10, minItems: 1, maxItems: 2)]],
        );

        expect($validator->passes())->toBeTrue();
    });

    it('fails on item count before checking translation lengths', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'Value', 'pt' => 'Valor', 'fr' => 'Valeur']],
            ['field' => [new Translatable(minLength: 3, maxLength: 10, minItems: 1, maxItems: 2)]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.array.max', ['attribute' => 'field', 'max' => 2]));
    });

    it('fails on translation length when item count is valid', function () {
        $validator = Validator::make(
            ['field' => ['en' => 'this translation is too long', 'pt' => 'Valor']],
            ['field' => [new Translatable(minLength: 3, maxLength: 10, minItems: 1, maxItems: 2)]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.string.max', ['attribute' => 'field', 'max' => 10]));
    });

    it('fails when the value is not an array or string', function () {
        $validator = Validator::make(
            ['field' => 123],
            ['field' => [new Translatable()]],
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('field'))
            ->toBe(__('validation.translatable.type', ['attribute' => 'field']));
    });
});
