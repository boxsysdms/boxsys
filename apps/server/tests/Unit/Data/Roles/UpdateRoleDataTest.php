<?php

declare(strict_types=1);

use App\Data\Roles\UpdateRoleData;

uses()->group('unit', 'data', 'roles');

describe('instantiation', function () {
    it('ccan be instantiated with translated display name', function () {
        $dto = new UpdateRoleData(
            displayName: ['en' => 'Updated name'],
            description: 'Updated description',
        );

        expect($dto)->toBeInstanceOf(UpdateRoleData::class);

        expect($dto)
            ->displayName->toBe(['en' => 'Updated name'])
            ->description->toBe('Updated description');
    });

    it('can be instantiated with translated description', function () {
        $dto = new UpdateRoleData(
            displayName: 'Updated name',
            description: ['en' => 'Updated description'],
        );

        expect($dto)->toBeInstanceOf(UpdateRoleData::class);

        expect($dto)
            ->displayName->toBe('Updated name')
            ->description->toBe(['en' => 'Updated description']);
    });

    it('can be instantiated without any data', function () {
        $dto = new UpdateRoleData();

        expect($dto)
            ->displayName->toBeNull()
            ->description->toBeNull();
    });
});
