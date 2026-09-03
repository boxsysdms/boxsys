<?php

declare(strict_types=1);

uses()->group('unit', 'arch');

arch()->preset()->php();
arch()->preset()->laravel();
arch()->preset()->security();

arch('avoid inheritance')
    ->expect('App')
    ->classes()
    ->toExtendNothing()
    ->ignoring([
        'App\Data',
        'App\Exceptions',
        'App\Http',
        'App\Jobs',
        'App\Models',
        'App\Providers',
        'App\Services',
    ]);

arch('factories')
    ->expect('Database\Factories')
    ->toExtend(Illuminate\Database\Eloquent\Factories\Factory::class)
    ->toHaveMethod('definition')
    ->toOnlyBeUsedIn([
        'App\Models',
    ]);

arch('actions')
    ->expect('App\Actions')
    ->toHaveMethod('handle');

arch('models')
    ->expect('App\Models')
    ->toHaveMethod('casts')
    ->toOnlyBeUsedIn([
        'App\Actions',
        'App\Http',
        'App\Jobs',
        'App\Models',
        'App\Policies',
        'App\Providers',
        'App\Services',
        'Database\Factories',
        'Database\Seeders',
    ]);
