<?php

namespace App\Policies;

use App\Models\Reunion;
use App\Models\User;

class ReunionPolicy
{
    public function create(User $user): bool
    {
        return $user->canManage();
    }

    public function update(User $user, Reunion $reunion): bool
    {
        return $user->canManage();
    }

    public function delete(User $user, Reunion $reunion): bool
    {
        return $user->canManage();
    }
}
