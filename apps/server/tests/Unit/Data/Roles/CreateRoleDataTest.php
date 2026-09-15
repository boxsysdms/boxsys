<?php

declare(strict_types=1);

use App\Data\Roles\CreateRoleData;
use App\Enums\PermissionScope;

uses()->group('unit', 'data', 'roles');

describe('instantiation', function () {
    it('can be instantiated with translated fields', function () {
        $dto = new CreateRoleData(
            scope: PermissionScope::SYSTEM,
            displayName: ['en' => 'Admin', 'pt' => 'Administrador'],
            description: ['en' => 'System role', 'pt' => 'Função do sistema'],
        );

        expect($dto)->toBeInstanceOf(CreateRoleData::class);

        expect($dto)
            ->scope->toBe(PermissionScope::SYSTEM)
            ->displayName->toBe(['en' => 'Admin', 'pt' => 'Administrador'])
            ->description->toBe(['en' => 'System role', 'pt' => 'Função do sistema']);
    });

    it('can be instantiated with string fields', function () {
        $dto = new CreateRoleData(
            scope: PermissionScope::COLLECTION->value,
            displayName: 'Collection manager',
            description: 'Collection role',
        );

        expect($dto)
            ->displayName->toBe('Collection manager')
            ->description->toBe('Collection role');
    });
});
