<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Illuminate\Database\Eloquent\Model;

/**
 * Helper function to test soft delete functionality in Eloquent models.
 *
 * This helper only verifies that the model uses the SoftDeletes trait, but
 * does not perform actual soft delete operations, since those are already
 * covered in the framework's own tests.
 *
 * @param  class-string<Model>  $class  The model class to be tested.
 */
function testSoftDeletes(string $class): void
{
    describe('uses soft deletes', function () use ($class) {
        it('uses SoftDeletes trait', function () use ($class) {
            $traits = class_uses($class);

            expect($traits)->toContain(\Illuminate\Database\Eloquent\SoftDeletes::class);
        });
    });
}

/**
 * Helper function to test UUID field functionality in Eloquent models.
 *
 * There no need to cover all case on all models that use UUIDs. We only need
 * one model to be fully tested, since the HasUuid trait is responsible for all
 * the logic. For other models, it's enough to verify that they use the trait
 * and have the expected field.
 *
 * @param  class-string<Model>  $class  The model class to be tested.
 */
function testUuidField(string $class, bool $fullTest = false): void
{
    describe('UUID', function () use ($class, $fullTest) {
        it('uses the HasUuid trait', function () use ($class) {
            $traits = class_uses($class);

            expect($traits)->toContain(\App\Traits\HasUuid::class);
        });

        if (! $fullTest) {
            return;
        }

        it('generates a UUID automatically when creating a model', function () use ($class) {
            $model = $class::factory()->create(['uuid' => null]);

            expect($model->uuid)->toBeUuid();
        });

        it('retrieves a model by its UUID', function () use ($class) {
            $model = $class::factory()->create();
            $found = $class::findByUuid($model->uuid);

            expect($found)
                ->not->toBeNull()
                ->id->toBe($model->id)
                ->uuid->toBe($model->uuid);
        });

        it('returns null when the UUID does not exist', function () use ($class) {
            expect($class::findByUuid('non-existent-uuid'))->toBeNull();
        });

        it('throws an exception when the UUID does not exist', function () use ($class) {
            $class::findByUuidOrFail('non-existent-uuid');
        })->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        it('excludes soft deleted models from UUID lookup', function () use ($class) {
            $model = tap($class::factory()->create(), fn(Model $model) => $model->delete());

            expect($class::findByUuid($model->uuid))->toBeNull();
        });
    });
}
