<?php

namespace App\Policies;

use App\Models\ArchivePartage;
use App\Models\User;

class ArchivePartagePolicy
{
    public function delete(User $user, ArchivePartage $partage): bool
    {
        return $partage->partage_par === $user->id;
    }
}
