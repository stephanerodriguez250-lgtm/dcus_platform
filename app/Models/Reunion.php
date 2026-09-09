<?php

namespace App\Models;

use App\Models\Concerns\HasHashedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reunion extends Model
{
    use HasFactory, HasHashedRouteKey;

    protected $fillable = [
        'titre', 'date', 'heure', 'lieu',
        'ordre_du_jour', 'compte_rendu',
        'statut', 'convocateur', 'created_by',
    ];

    protected $casts = ['date' => 'date'];

    public static array $statuts = [
        'planifiee' => 'Planifiée',
        'en_cours' => 'En cours',
        'terminee' => 'Terminée',
        'annulee' => 'Annulée',
    ];

    public static array $statutColors = [
        'planifiee' => 'primary',
        'en_cours' => 'warning',
        'terminee' => 'success',
        'annulee' => 'danger',
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

    public function decisions()
    {
        return $this->hasMany(Decision::class, 'reunion_id')->orderBy('created_at');
    }
}
