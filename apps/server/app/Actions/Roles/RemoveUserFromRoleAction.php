<?php

declare(strict_types=1);

namespace App\Actions\Roles;

use App\Data\Users\UserIdentifiersData;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Remove users from a role.
 *
 * The provided identifiers may be UUIDs or email addresses. UUIDs are
 * matched against the `uuid` column, while email addresses are matched
 * against the `email` column. Distinct user records are retrieved from
 * the database and limited to a maximum of 50 per request.
 *
 * Once resolved, the users are detached from the specified role through
 * the many-to-many relationship between roles and users.
 */
final class RemoveUserFromRoleAction
{
    /**
     * Handle user removal from a role.
     */
    public function handle(Role $role, UserIdentifiersData $usersData): void
    {
        $uuids = array_filter($usersData->users, fn ($uuid) => str()->isUuid($uuid));
        $emails = array_filter($usersData->users, fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));

        DB::transaction(function () use ($role, $uuids, $emails) {
            $users = User::query()
                ->distinct()
                ->select('id')
                ->where(function ($query) use ($uuids, $emails) {
                    $query->whereIn('uuid', $uuids)
                        ->orWhereIn('email', $emails);
                })
                ->limit(50)
                ->get();

            $role->removeFromModels($users->pluck('id')->all(), User::class);
        });
    }
}
