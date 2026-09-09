<?php

namespace App\Models;

use App\Models\Concerns\HasHashedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * $avis n'est PAS la conclusion de la fiche : c'est le numéro d'avis (identifiant unique de
 * la fiche, ex. "0053"), saisi manuellement par l'agent et inscrit sur le document généré
 * après "Avis N°". La Conclusion, elle, ne se saisit plus du tout : elle est générée à partir
 * de l'accord (voir Accord::$conclusion_appreciation).
 */
class AccordAppreciation extends Model
{
    use HasFactory, HasHashedRouteKey;

    protected $fillable = [
        'accord_id', 'origine', 'objet', 'avis',
        'observations_forme', 'observations_fond',
        'redige_par', 'chemin_fiche_word',
    ];

    public function accord()
    {
        return $this->belongsTo(Accord::class);
    }

    public function redacteur()
    {
        return $this->belongsTo(User::class, 'redige_par');
    }
}
