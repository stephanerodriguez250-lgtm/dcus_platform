<?php

namespace App\Models;

use App\Models\Concerns\HasHashedRouteKey;
use Illuminate\Database\Eloquent\Model;

class CodirRapport extends Model
{
    use HasHashedRouteKey;

    protected $fillable = [
        'codir_id', 'nom_fichier', 'chemin_fichier',
        'type_fichier', 'taille', 'uploaded_by',
    ];

    public function codir()
    {
        return $this->belongsTo(Codir::class);
    }

    public function uploadeur()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
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
}
