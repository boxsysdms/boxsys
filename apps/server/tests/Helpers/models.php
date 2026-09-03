<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Illuminate\Database\Eloquent\Model;

/**
 * Helper function to test soft delete functionality in Eloquent models.
 *
 * This helper only verifies that the model uses the SoftDeletes trait, but does not
 * perform actual soft delete operations, since those are already covered in the
 * framework's own tests.
 *
 * @param  class-string<Model>  $class
 */
function testSoftDeletes(string $class): void
{
    describe('soft deletes', function () use ($class) {
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
 * @param  class-string<Model>  $class
 */
function testUuidField(string $class, bool $fullTest = false): void
{
    describe('uuid field', function () use ($class, $fullTest) {
        it('uses HasUuid trait', function () use ($class) {
            $traits = class_uses($class);

            expect($traits)->toContain(\App\Traits\HasUuid::class);
        });

        if (! $fullTest) {
            return;
        }

        it('generates uuid automatically on creation', function () use ($class) {
            $model = $class::factory()->create(['uuid' => null]);

            expect($model->uuid)->toBeUuid();
        });

        it('finds model by uuid', function () use ($class) {
            $model = $class::factory()->create();
            $found = $class::findByUuid($model->uuid);

            expect($found)
                ->not->toBeNull()
                ->id->toBe($model->id)
                ->uuid->toBe($model->uuid);
        });

        it('returns null for nonexistent uuid', function () use ($class) {
            expect($class::findByUuid('non-existent-uuid'))->toBeNull();
        });

        it('finds model by uuid or fails', function () use ($class) {
            $class::findByUuidOrFail('non-existent-uuid');
        })->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        it('does not return soft deleted models by default', function () use ($class) {
            $model = tap($class::factory()->create(), fn (Model $model) => $model->delete());

            expect($class::findByUuid($model->uuid))->toBeNull();
        });
    });
}
