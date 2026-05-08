<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Codir extends Model
{
    use HasFactory;

    protected $fillable = [
        'objet', 'date', 'heure_debut', 'heure_fin', 'lieu',
        'presidente', 'rapporteur', 'synthese', 'decisions',
        'divers', 'prochaine_reunion', 'statut', 'created_by',
    ];

    protected $casts = [
        'date'              => 'date',
        'prochaine_reunion' => 'date',
    ];

    public static array $statuts = [
        'planifie' => 'Planifié',
        'tenu'     => 'Tenu',
        'annule'   => 'Annulé',
    ];

    public static array $statutColors = [
        'planifie' => 'primary',
        'tenu'     => 'success',
        'annule'   => 'danger',
    ];

    public function getStatutLabelAttribute(): string
    {
        return self::$statuts[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return self::$statutColors[$this->statut] ?? 'secondary';
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->hasMany(CodirParticipant::class, 'codir_id');
    }

    public function rapports()
    {
        return $this->hasMany(CodirRapport::class, 'codir_id');
    }

    public function acces()
    {
        return $this->hasMany(CodirAcces::class, 'codir_id');
    }

    // CORRECTION : renommé decisions_list() → decisions() pour que le PDF fonctionne
    public function decisions()
    {
        return $this->hasMany(Decision::class, 'codir_id')->orderBy('created_at');
    }

    public function userPeutTelecharger(int $userId): bool
    {
        return $this->acces()->where('user_id', $userId)->exists();
    }
}