<?php

namespace App\Policies;

use App\Models\ArchiveFichier;
use App\Models\User;

class ArchiveFichierPolicy
{
    public function view(User $user, ArchiveFichier $fichier): bool
    {
        return $fichier->user_id === $user->id;
    }

    public function update(User $user, ArchiveFichier $fichier): bool
    {
        return $fichier->user_id === $user->id;
    }

    public function delete(User $user, ArchiveFichier $fichier): bool
    {
        return $fichier->user_id === $user->id;
    }
}
