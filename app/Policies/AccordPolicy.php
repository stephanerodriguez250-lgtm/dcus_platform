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

    /**
     * Laisser une appréciation sur un accord (étape 2) : gestionnaires, ou agent explicitement
     * autorisé via AccordAppreciateur.
     */
    public function apprecier(User $user): bool
    {
        return $user->peutApprecierAccords();
    }
}
