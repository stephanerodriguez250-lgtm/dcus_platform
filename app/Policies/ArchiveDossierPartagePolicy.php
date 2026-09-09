<?php

namespace App\Policies;

use App\Models\ArchiveDossierPartage;
use App\Models\User;

class ArchiveDossierPartagePolicy
{
    public function delete(User $user, ArchiveDossierPartage $partage): bool
    {
        return $partage->partage_par === $user->id;
    }

    public function download(User $user, ArchiveDossierPartage $partage): bool
    {
        return $partage->destinataire_id === $user->id;
    }
}
