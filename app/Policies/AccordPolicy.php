<?php

namespace App\Policies;

use App\Models\Accord;
use App\Models\User;

class AccordPolicy
{
    public function create(User $user): bool
    {
        return $user->canManage();
    }

    public function update(User $user, Accord $accord): bool
    {
        return $user->canManage();
    }

    public function delete(User $user, Accord $accord): bool
    {
        return $user->canManage();
    }
}
