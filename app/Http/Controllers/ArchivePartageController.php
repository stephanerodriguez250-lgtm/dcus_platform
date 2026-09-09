<?php

namespace App\Http\Controllers;

use App\Models\ArchiveFichier;
use App\Models\ArchivePartage;
use App\Models\User;
use App\Notifications\FichierPartageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArchivePartageController extends Controller
{
    public function index()
    {
        $partages = ArchivePartage::where('partage_par', Auth::id())
            ->with(['fichierOriginal', 'fichierCopie', 'destinataire'])
            ->latest()
            ->get();

        return view('archives.partages.index', compact('partages'));
    }

    public function destroy(ArchivePartage $partage)
    {
        $this->authorize('delete', $partage);

        $partage->delete();

        return redirect()->route('archives.partages.index')->with('success', 'Partage révoqué.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fichier_ids' => 'required|array|min:1',
            'fichier_ids.*' => 'integer|exists:archive_fichiers,id',
            'destinataire_ids' => 'required|array|min:1',
            'destinataire_ids.*' => [
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ((int) $value === Auth::id()) {
                        $fail('Vous ne pouvez pas vous partager un fichier à vous-même.');
                    }
                },
            ],
            'retour_dossier_id' => 'nullable|exists:archive_folders,id',
        ]);

        $fichiers = ArchiveFichier::whereIn('id', $validated['fichier_ids'])
            ->where('user_id', Auth::id())
            ->get();

        if ($fichiers->count() !== count($validated['fichier_ids'])) {
            abort(403);
        }

        $expediteur = Auth::user();
        $destinataires = User::whereIn('id', $validated['destinataire_ids'])->get();

        foreach ($fichiers as $fichier) {
            foreach ($destinataires as $destinataire) {
                $copie = $this->copierFichierPour($fichier, $destinataire);

                ArchivePartage::create([
                    'fichier_original_id' => $fichier->id,
                    'fichier_copie_id' => $copie->id,
                    'partage_par' => $expediteur->id,
                    'destinataire_id' => $destinataire->id,
                ]);

                $destinataire->notify(new FichierPartageNotification($fichier, $expediteur, $copie));
            }
        }

        $retourDossierId = $validated['retour_dossier_id'] ?? null;

        return redirect()->route('archives.index', $retourDossierId ? ['dossier' => $retourDossierId] : [])
            ->with('success', 'Fichier(s) partagé(s) avec succès.');
    }

    private function copierFichierPour(ArchiveFichier $fichier, User $destinataire): ArchiveFichier
    {
        $nouveauChemin = 'archives/fichiers/'.Str::random(40).'.'.$fichier->type_fichier;
        Storage::disk('public')->copy($fichier->chemin_fichier, $nouveauChemin);

        return ArchiveFichier::create([
            'user_id' => $destinataire->id,
            'folder_id' => null,
            'intitule' => $fichier->intitule,
            'numero' => $fichier->numero,
            'description' => $fichier->description,
            'nom_fichier' => $fichier->nom_fichier,
            'chemin_fichier' => $nouveauChemin,
            'type_fichier' => $fichier->type_fichier,
            'taille' => $fichier->taille,
        ]);
    }
}
