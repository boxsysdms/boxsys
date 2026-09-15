<?php

declare(strict_types=1);

namespace App\Data\Users;

use Spatie\LaravelData\Data;

final class UserIdentifiersData extends Data
{
    /**
     * @param  list<string>  $users
     */
    public function __construct(
        public readonly array $users
    ) {}
}
