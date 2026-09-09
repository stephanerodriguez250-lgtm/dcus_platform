<?php

namespace App\Models\Concerns;

use App\Support\IdHasher;

/**
 * Fait apparaître un identifiant haché (non séquentiel) dans les URLs générées via route(),
 * au lieu de l'ID auto-incrémenté brut — sans changer l'ID en base ni les relations, qui
 * restent des entiers classiques. `getRouteKey()` est appelé automatiquement par route()
 * quand on lui passe l'instance du modèle (pas un ->id nu — voir CLAUDE.md) ;
 * `resolveRouteBinding()` fait l'inverse pour le binding implicite de route ({model} dans
 * une signature de contrôleur). Un hash invalide/trafiqué décode vers null, donc
 * `->where(...)->first()` ne trouve rien et Laravel renvoie un 404 normal — pas d'erreur
 * spéciale à gérer.
 */
trait HasHashedRouteKey
{
    public function getRouteKey()
    {
        return IdHasher::encoder($this->getKey());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $id = IdHasher::decoder((string) $value);

        if ($id === null) {
            return null;
        }

        return $this->where($field ?? $this->getRouteKeyName(), $id)->first();
    }
}
