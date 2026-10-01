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
