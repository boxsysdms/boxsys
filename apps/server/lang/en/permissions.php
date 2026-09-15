<?php

declare(strict_types=1);

/**
 * Special language file used exclusively for seeding permissions into
 * the database.
 *
 * Each key defined in this file represents a permission that can be
 * assigned to roles. Unlike regular language files, this file is not
 * intended for application translations and must not be modified for
 * localization purposes.
 *
 * This file should never be translated or altered for user-facing
 * content. Its sole purpose is to provide a consistent source of
 * permission identifiers during seeding.
 */

return [
    'system' => [
        'roles' => [
            'create' => [
                'en' => 'Create role',
                'pt' => 'Criar função',
            ],
            'delete' => [
                'en' => 'Delete role',
                'pt' => 'Excluir função',
            ],
            'list' => [
                'en' => 'List roles',
                'pt' => 'Listar funções',
            ],
            'permissions' => [
                'en' => 'Manage role permissions',
                'pt' => 'Gerenciar permissões da função',
            ],
            'update' => [
                'en' => 'Update role',
                'pt' => 'Atualizar função',
            ],
            'users' => [
                'en' => 'Manage role users',
                'pt' => 'Gerenciar utilizadores da função',
            ],
            'view' => [
                'en' => 'View role',
                'pt' => 'Visualizar função',
            ],
        ],
    ],
];
