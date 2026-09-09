<?php

namespace App\Models;

use App\Models\Concerns\HasHashedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ArchiveFolder extends Model
{
    use HasFactory, HasHashedRouteKey;

    protected $fillable = [
        'user_id', 'parent_id', 'nom',
    ];

    public function proprietaire()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(ArchiveFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ArchiveFolder::class, 'parent_id');
    }

    public function fichiers()
    {
        return $this->hasMany(ArchiveFichier::class, 'folder_id');
    }

    /**
     * Chemin du dossier racine jusqu'à ce dossier (inclus), pour le fil d'Ariane.
     */
    public function filAriane(): array
    {
        $chemin = [];
        $dossier = $this;

        while ($dossier !== null) {
            array_unshift($chemin, $dossier);
            $dossier = $dossier->parent;
        }

        return $chemin;
    }

    /**
     * Tous les fichiers de ce dossier et de ses sous-dossiers, à tous les niveaux.
     */
    public function fichiersRecursifs(): Collection
    {
        $fichiers = $this->fichiers()->get();

        foreach ($this->children as $enfant) {
            $fichiers = $fichiers->merge($enfant->fichiersRecursifs());
        }

        return $fichiers;
    }

    /**
     * Tous les sous-dossiers de ce dossier, à tous les niveaux (pour éviter les déplacements circulaires).
     */
    public function descendantsRecursifs(): Collection
    {
        $descendants = $this->children;

        foreach ($this->children as $enfant) {
            $descendants = $descendants->merge($enfant->descendantsRecursifs());
        }

        return $descendants;
    }
}
