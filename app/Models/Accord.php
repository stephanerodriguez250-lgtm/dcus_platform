<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Accord extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre', 'institution_partenaire', 'reference',
        'date_arrivee', 'heure_arrivee',
        'chemin_fichier', 'nom_fichier',
        'envoye_le',
        'date_signature', 'chemin_fichier_signe', 'nom_fichier_signe',
        'duree_valeur', 'duree_unite', 'date_expiration',
        'alerte_expiration_envoyee_le',
        'created_by',
    ];

    protected $casts = [
        'date_arrivee' => 'date',
        'envoye_le' => 'date',
        'date_signature' => 'date',
        'date_expiration' => 'date',
        'alerte_expiration_envoyee_le' => 'datetime',
        // Sans ce cast, `duree_valeur` reste une chaîne quand elle vient directement d'une
        // requête HTTP (la règle de validation "integer" ne fait que valider le format, elle
        // ne caste pas) — Carbon::addYears()/addMonths() refuse alors une chaîne (TypeError).
        'duree_valeur' => 'integer',
    ];

    public static array $dureeUnites = [
        'mois' => 'mois',
        'ans' => 'ans',
    ];

    public static array $etapeLabels = [
        'recu' => 'Reçu',
        'apprecie' => 'Apprécié',
        'envoye' => 'Envoyé',
        'signe' => 'Signé',
    ];

    public static array $etapeColors = [
        'recu' => 'secondary',
        'apprecie' => 'info',
        'envoye' => 'warning',
        'signe' => 'success',
    ];

    // --- Relations ---
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function appreciation()
    {
        return $this->hasOne(AccordAppreciation::class);
    }

    public function historiques()
    {
        return $this->hasMany(AccordHistorique::class)->orderByDesc('date_modification');
    }

    public function rapportConformite()
    {
        return $this->hasOne(AccordRapportConformite::class);
    }

    // --- Étape calculée (le workflow n'a pas de statut stocké, il se déduit des données) ---
    public function getEtapeAttribute(): string
    {
        if ($this->date_signature) {
            return 'signe';
        }
        if ($this->envoye_le) {
            return 'envoye';
        }
        if ($this->appreciation) {
            return 'apprecie';
        }

        return 'recu';
    }

    public function getEtapeLabelAttribute(): string
    {
        return self::$etapeLabels[$this->etape];
    }

    public function getEtapeColorAttribute(): string
    {
        return self::$etapeColors[$this->etape];
    }

    public function getDureeLabelAttribute(): ?string
    {
        if (! $this->duree_valeur || ! $this->duree_unite) {
            return null;
        }

        return $this->duree_valeur.' '.self::$dureeUnites[$this->duree_unite];
    }

    public function calculerDateExpiration(): ?Carbon
    {
        if (! $this->date_signature || ! $this->duree_valeur || ! $this->duree_unite) {
            return null;
        }

        return $this->duree_unite === 'ans'
            ? $this->date_signature->copy()->addYears($this->duree_valeur)
            : $this->date_signature->copy()->addMonths($this->duree_valeur);
    }

    /**
     * Conclusion générée automatiquement pour la fiche d'appréciation : formulation fixe,
     * imposée par la DCUS, où seul l'intitulé de l'accord varie. Elle ne se saisit plus
     * manuellement (voir AccordAppreciation::$avis, qui sert désormais à autre chose : le
     * numéro d'avis inscrit en tête de la fiche).
     */
    public function getConclusionAppreciationAttribute(): string
    {
        return "Au regard de tout ce qui précède, le processus de signature de l'"
            .Str::lcfirst($this->titre)
            .' peut être enclenché, sous réserve de la prise en compte des observations faites.';
    }
}
