<?php

namespace App\Policies;

use App\Models\ArchiveFolder;
use App\Models\User;

class ArchiveFolderPolicy
{
    public function view(User $user, ArchiveFolder $dossier): bool
    {
        return $dossier->user_id === $user->id;
    }

    public function update(User $user, ArchiveFolder $dossier): bool
    {
        return $dossier->user_id === $user->id;
    }

    public function delete(User $user, ArchiveFolder $dossier): bool
    {
        return $dossier->user_id === $user->id;
    }
}
