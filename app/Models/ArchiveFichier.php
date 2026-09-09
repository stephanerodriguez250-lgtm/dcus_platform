<?php

namespace App\Models;

use App\Models\Concerns\HasHashedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveFichier extends Model
{
    use HasFactory, HasHashedRouteKey;

    protected $fillable = [
        'user_id', 'folder_id', 'intitule', 'numero', 'description',
        'nom_fichier', 'chemin_fichier', 'type_fichier', 'taille',
    ];

    protected static array $icones = [
        'pdf' => 'bi-file-earmark-pdf text-danger',
        'doc' => 'bi-file-earmark-word text-primary',
        'docx' => 'bi-file-earmark-word text-primary',
        'xls' => 'bi-file-earmark-excel text-success',
        'xlsx' => 'bi-file-earmark-excel text-success',
        'jpg' => 'bi-file-earmark-image text-warning',
        'jpeg' => 'bi-file-earmark-image text-warning',
        'png' => 'bi-file-earmark-image text-warning',
    ];

    public function proprietaire()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dossier()
    {
        return $this->belongsTo(ArchiveFolder::class, 'folder_id');
    }

    public function partageOrigine()
    {
        return $this->hasOne(ArchivePartage::class, 'fichier_copie_id');
    }

    public function getTailleFormateeAttribute(): string
    {
        $bytes = $this->taille ?? 0;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' Mo';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' Ko';
        }

        return $bytes.' o';
    }

    public function getIconeAttribute(): string
    {
        return self::$icones[strtolower($this->type_fichier)] ?? 'bi-file-earmark text-secondary';
    }
}
