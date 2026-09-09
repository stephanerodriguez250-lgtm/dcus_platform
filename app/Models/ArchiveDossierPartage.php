<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveDossierPartage extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_original_id', 'dossier_copie_id', 'zip_path', 'partage_par', 'destinataire_id',
    ];

    public function dossierOriginal()
    {
        return $this->belongsTo(ArchiveFolder::class, 'dossier_original_id');
    }

    public function dossierCopie()
    {
        return $this->belongsTo(ArchiveFolder::class, 'dossier_copie_id');
    }

    public function partagePar()
    {
        return $this->belongsTo(User::class, 'partage_par');
    }

    public function destinataire()
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }
}
