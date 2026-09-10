<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Exemples de fiches d'appréciation fixes, choisis manuellement par la DCUS et servant de
 * few-shot à App\Services\AccordAppreciationSuggestionGenerator pour rédiger de nouvelles
 * fiches. Contrairement à AccordAppreciation, ces enregistrements ne sont liés à aucun
 * Accord réel — seul le style/les critères d'appréciation intéressent le prompt, jamais le
 * titre ou la référence d'un accord. La liste est fixe (voir
 * Database\Seeders\AccordAppreciationExempleSeeder) : elle ne varie plus avec les
 * appréciations réellement saisies par les agents, contrairement à l'ancien comportement
 * ("les 5 dernières appréciations").
 */
class AccordAppreciationExemple extends Model
{
    protected $fillable = ['ordre', 'origine', 'objet', 'observations_forme', 'observations_fond'];
}
