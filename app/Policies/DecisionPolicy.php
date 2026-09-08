<?php

namespace App\Policies;

use App\Models\Decision;
use App\Models\User;

class DecisionPolicy
{
    public function create(User $user): bool
    {
        return $user->canManage();
    }

    public function update(User $user, Decision $decision): bool
    {
        return $user->canManage();
    }

    public function delete(User $user, Decision $decision): bool
    {
        return $user->canManage();
    }
}
