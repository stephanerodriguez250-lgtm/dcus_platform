<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accord extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre', 'institution_partenaire', 'pays_partenaire',
        'universite_beneficiaire', 'description',
        'date_identification', 'date_signature', 'date_expiration',
        'statut', 'reunion_id', 'created_by',
    ];

    protected $casts = [
        'date_identification' => 'date',
        'date_signature' => 'date',
        'date_expiration' => 'date',
    ];

    public static array $statuts = [
        'identifie' => 'Identifié',
        'en_negotiation' => 'En négociation',
        'signe' => 'Signé',
        'en_execution' => 'En exécution',
        'cloture' => 'Clôturé',
        'abandonne' => 'Abandonné',
    ];

    public static array $statutColors = [
        'identifie' => 'secondary',
        'en_negotiation' => 'warning',
        'signe' => 'info',
        'en_execution' => 'primary',
        'cloture' => 'success',
        'abandonne' => 'danger',
    ];

    public function getStatutLabelAttribute(): string
    {
        return self::$statuts[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return self::$statutColors[$this->statut] ?? 'secondary';
    }

    // Relations
    public function reunion()
    {
        return $this->belongsTo(Reunion::class, 'reunion_id');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function historiques()
    {
        return $this->hasMany(AccordHistorique::class, 'accord_id')
            ->orderByDesc('date_modification');
    }
}
