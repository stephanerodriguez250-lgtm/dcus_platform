<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecisionHistorique extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'decision_id', 'ancien_statut', 'nouveau_statut',
        'progression', 'commentaire', 'modifie_par',
        'date_modification',
    ];

    protected $casts = [
        'date_modification' => 'datetime',
        'progression' => 'integer',
    ];

    public function decision()
    {
        return $this->belongsTo(Decision::class);
    }

    public function modificateur()
    {
        return $this->belongsTo(User::class, 'modifie_par');
    }

    public function getAncienStatutLabelAttribute(): string
    {
        return Decision::$statuts[$this->ancien_statut] ?? ($this->ancien_statut ?? '—');
    }

    public function getNouveauStatutLabelAttribute(): string
    {
        return Decision::$statuts[$this->nouveau_statut] ?? $this->nouveau_statut;
    }
}
