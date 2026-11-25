<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Tests\Helpers\testSoftDeletes;
use function Tests\Helpers\testUuidField;

uses(RefreshDatabase::class)->group('unit', 'models', 'users');

describe('model structure', function () {
    it('has correct array keys and types', function () {
        $user = User::factory()->create()->refresh();

        expect($user->toArray())
            ->toHaveKeys([
                'id',
                'uuid',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->and($user)
            ->id->toBeInt()
            ->uuid->toBeUuid()
            ->name->toBeString()
            ->email->toBeString()
            ->email_verified_at->toBeInstanceOf(Carbon\CarbonImmutable::class)
            ->created_at->toBeInstanceOf(Carbon\CarbonImmutable::class)
            ->updated_at->toBeInstanceOf(Carbon\CarbonImmutable::class)
            ->deleted_at->toBeNull();
    });
});

describe('api tokens', function () {
    it('can generate api tokens', function () {
        $user = User::factory()->create();

        $token = $user->createToken('Test Token');

        expect($token->plainTextToken)->toBeString();
    });
});

testSoftDeletes(User::class);

testUuidField(User::class, fullTest: true);
