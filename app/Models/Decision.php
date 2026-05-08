<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Decision extends Model
{
    protected $fillable = [
        'codir_id', 'reunion_id',
        'source_type',
        'note_numero', 'note_date', 'note_expediteur', 'note_objet', 'note_fichier',
        'intitule', 'responsable',
        'echeance', 'statut', 'progression', 'commentaire',
        'created_by',
    ];

    protected $casts = [
        'echeance'    => 'date',
        'note_date'   => 'date',
        'progression' => 'integer',
    ];

    public static array $statuts = [
        'assignee' => 'Assignée',
        'en_cours' => 'En cours',
        'validee'  => 'Validée',
        'cloturee' => 'Clôturée',
        'annulee'  => 'Annulée',
    ];

    public static array $statutColors = [
        'assignee' => 'secondary',
        'en_cours' => 'warning',
        'validee'  => 'info',
        'cloturee' => 'success',
        'annulee'  => 'danger',
    ];

    // CORRECTION 1 : 'annulee' => 0 était sorti du tableau par erreur
    public static array $statutProgression = [
        'assignee' => 0,
        'en_cours' => 50,
        'validee'  => 80,
        'cloturee' => 100,
        'annulee'  => 0,
    ];

    public static array $sourceTypeLabels = [
        'codir_interne'      => 'CODIR Interne',
        'codir_externe'      => 'CODIR Externe',
        'reunion_interne'    => 'Réunion Interne',
        'reunion_externe'    => 'Réunion Externe',
        'note_ministerielle' => 'Note Ministérielle',
    ];

    public static array $sourceTypeColors = [
        'codir_interne'      => '1a3a5c',
        'codir_externe'      => '0d6efd',
        'reunion_interne'    => '6f42c1',
        'reunion_externe'    => '0dcaf0',
        'note_ministerielle' => 'ffc107',
    ];

    // ── Accesseurs ──────────────────────────────────────────────────

    public function getStatutLabelAttribute(): string
    {
        return self::$statuts[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return self::$statutColors[$this->statut] ?? 'secondary';
    }

    public function getProgressionColorAttribute(): string
    {
        if ($this->progression >= 100) return 'success';
        if ($this->progression >= 60)  return 'info';
        if ($this->progression >= 30)  return 'warning';
        return 'danger';
    }

    public function getSourceTypeLabelAttribute(): string
    {
        return self::$sourceTypeLabels[$this->source_type] ?? '—';
    }

    public function getSourceTypeColorAttribute(): string
    {
        return self::$sourceTypeColors[$this->source_type] ?? '6c757d';
    }

    public function getSourceResumeAttribute(): string
    {
        if ($this->source_type === 'note_ministerielle') {
            $parts = array_filter([
                $this->note_numero,
                $this->note_date?->format('d/m/Y'),
            ]);
            return implode(' – ', $parts) ?: 'Note ministérielle';
        }

        if ($this->codir) {
            return ($this->codir->objet ?? 'CODIR') . ' – ' . $this->codir->date->format('d/m/Y');
        }

        if ($this->reunion) {
            return ($this->reunion->titre ?? 'Réunion') . ' – ' . $this->reunion->date->format('d/m/Y');
        }

        return '—';
    }

    public function isEnRetard(): bool
    {
        return $this->echeance
            && $this->echeance->isPast()
            && !in_array($this->statut, ['cloturee', 'annulee']);
    }

    // ── Relations ───────────────────────────────────────────────────

    public function codir()
    {
        return $this->belongsTo(Codir::class);
    }

    public function reunion()
    {
        return $this->belongsTo(Reunion::class);
    }

    // CORRECTION 2 : return était sorti de la fonction createur() par erreur
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function historiques()
    {
        return $this->hasMany(DecisionHistorique::class)
                    ->orderByDesc('date_modification');
    }
}