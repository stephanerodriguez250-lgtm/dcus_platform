<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivePartage extends Model
{
    use HasFactory;

    protected $fillable = [
        'fichier_original_id', 'fichier_copie_id', 'partage_par', 'destinataire_id',
    ];

    public function fichierOriginal()
    {
        return $this->belongsTo(ArchiveFichier::class, 'fichier_original_id');
    }

    public function fichierCopie()
    {
        return $this->belongsTo(ArchiveFichier::class, 'fichier_copie_id');
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
