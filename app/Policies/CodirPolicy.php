<?php

namespace App\Policies;

use App\Models\Codir;
use App\Models\User;

class CodirPolicy
{
    public function create(User $user): bool
    {
        return $user->canManage();
    }

    public function update(User $user, Codir $codir): bool
    {
        return $user->canManage();
    }

    public function delete(User $user, Codir $codir): bool
    {
        return $user->canManage();
    }

    /**
     * Gérer les rapports/accès d'un CODIR (upload, suppression, octroi d'accès) : admin uniquement.
     */
    public function administer(User $user, Codir $codir): bool
    {
        return $user->isAdmin();
    }

    /**
     * Télécharger le compte rendu / les rapports d'un CODIR : admin ou accès nominatif accordé.
     */
    public function download(User $user, Codir $codir): bool
    {
        return $user->isAdmin() || $codir->userPeutTelecharger($user->id);
    }
}
