<?php

namespace App\Support;

use Hashids\Hashids;

/**
 * Encode/décode les identifiants numériques utilisés dans les URLs de l'application, pour
 * ne jamais exposer d'ID auto-incrémenté séquentiel dans un lien (énumération triviale d'un
 * accord/réunion/utilisateur à l'autre). Le sel dérive de APP_KEY : pas de nouvelle variable
 * d'environnement à gérer, et le résultat est stable pour une même installation tout en
 * restant imprévisible pour quiconque n'a pas cette clé.
 */
class IdHasher
{
    private static ?Hashids $instance = null;

    public static function instance(): Hashids
    {
        return self::$instance ??= new Hashids((string) config('app.key'), 8);
    }

    public static function encoder(int $id): string
    {
        return self::instance()->encode($id);
    }

    public static function decoder(string $hash): ?int
    {
        $ids = self::instance()->decode($hash);

        return $ids[0] ?? null;
    }
}
