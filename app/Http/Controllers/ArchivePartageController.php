<?php

namespace App\Http\Controllers;

use App\Models\ArchiveDossierPartage;
use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
use App\Models\ArchivePartage;
use App\Models\User;
use App\Notifications\DossierPartageNotification;
use App\Notifications\FichierPartageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ArchivePartageController extends Controller
{
    public function index()
    {
        $partages = ArchivePartage::where('partage_par', Auth::id())
            ->with(['fichierOriginal', 'fichierCopie', 'destinataire'])
            ->latest()
            ->get();

        $dossierPartages = ArchiveDossierPartage::where('partage_par', Auth::id())
            ->with(['dossierOriginal', 'dossierCopie', 'destinataire'])
            ->latest()
            ->get();

        return view('archives.partages.index', compact('partages', 'dossierPartages'));
    }

    public function destroy(ArchivePartage $partage)
    {
        $this->authorize('delete', $partage);

        $partage->delete();

        return redirect()->route('archives.partages.index')->with('success', 'Partage révoqué.');
    }

    public function destroyDossier(ArchiveDossierPartage $partage)
    {
        $this->authorize('delete', $partage);

        if ($partage->zip_path) {
            Storage::disk('public')->delete($partage->zip_path);
        }

        $partage->delete();

        return redirect()->route('archives.partages.index')->with('success', 'Partage révoqué.');
    }

    public function telechargerDossier(ArchiveDossierPartage $partage)
    {
        $this->authorize('download', $partage);

        if (! $partage->zip_path || ! Storage::disk('public')->exists($partage->zip_path)) {
            return back()->with('error', 'Archive introuvable.');
        }

        $nom = ($partage->dossierOriginal->nom ?? $partage->dossierCopie->nom).'.zip';

        return Storage::disk('public')->download($partage->zip_path, $nom);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fichier_ids' => 'nullable|array',
            'fichier_ids.*' => 'integer|exists:archive_fichiers,id',
            'dossier_ids' => 'nullable|array',
            'dossier_ids.*' => 'integer|exists:archive_folders,id',
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
            'note' => 'nullable|string|max:1000',
        ]);

        $note = $validated['note'] ?? null;

        $fichierIds = $validated['fichier_ids'] ?? [];
        $dossierIds = $validated['dossier_ids'] ?? [];

        if (empty($fichierIds) && empty($dossierIds)) {
            return back()->withErrors(['fichier_ids' => 'Sélectionnez au moins un fichier ou un dossier à partager.'])->withInput();
        }

        $fichiers = ArchiveFichier::whereIn('id', $fichierIds)->where('user_id', Auth::id())->get();
        if ($fichiers->count() !== count($fichierIds)) {
            abort(403);
        }

        $dossiers = ArchiveFolder::whereIn('id', $dossierIds)->where('user_id', Auth::id())->get();
        if ($dossiers->count() !== count($dossierIds)) {
            abort(403);
        }

        $expediteur = Auth::user();
        $destinataires = User::whereIn('id', $validated['destinataire_ids'])->get();

        foreach ($fichiers as $fichier) {
            foreach ($destinataires as $destinataire) {
                $dossierCollecteur = $this->dossierPartagesRecusPour($destinataire);
                $copie = $this->copierFichierPour($fichier, $destinataire, $dossierCollecteur->id);

                ArchivePartage::create([
                    'fichier_original_id' => $fichier->id,
                    'fichier_copie_id' => $copie->id,
                    'partage_par' => $expediteur->id,
                    'destinataire_id' => $destinataire->id,
                    'note' => $note,
                ]);

                $destinataire->notify(new FichierPartageNotification($fichier, $expediteur, $copie, $note));
            }
        }

        foreach ($dossiers as $dossier) {
            foreach ($destinataires as $destinataire) {
                $dossierCollecteur = $this->dossierPartagesRecusPour($destinataire);
                $copieDossier = $this->copierDossierPour($dossier, $destinataire, $dossierCollecteur->id, verifierDoublon: true);
                $zipPath = $this->genererZipDossier($copieDossier);

                $partageDossier = ArchiveDossierPartage::create([
                    'dossier_original_id' => $dossier->id,
                    'dossier_copie_id' => $copieDossier->id,
                    'zip_path' => $zipPath,
                    'partage_par' => $expediteur->id,
                    'destinataire_id' => $destinataire->id,
                    'note' => $note,
                ]);

                $destinataire->notify(new DossierPartageNotification($dossier, $expediteur, $partageDossier, $note));
            }
        }

        $retourDossierId = $validated['retour_dossier_id'] ?? null;

        return redirect()->route('archives.index', $retourDossierId ? ['dossier' => $retourDossierId] : [])
            ->with('success', 'Fichier(s)/dossier(s) partagé(s) avec succès.');
    }

    private function copierFichierPour(ArchiveFichier $fichier, User $destinataire, ?int $folderId = null): ArchiveFichier
    {
        $nouveauChemin = 'archives/fichiers/'.Str::random(40).'.'.$fichier->type_fichier;
        Storage::disk('public')->copy($fichier->chemin_fichier, $nouveauChemin);

        return ArchiveFichier::create([
            'user_id' => $destinataire->id,
            'folder_id' => $folderId,
            'intitule' => $fichier->intitule,
            'numero' => $fichier->numero,
            'description' => $fichier->description,
            'nom_fichier' => $fichier->nom_fichier,
            'chemin_fichier' => $nouveauChemin,
            'type_fichier' => $fichier->type_fichier,
            'taille' => $fichier->taille,
        ]);
    }

    private function copierDossierPour(ArchiveFolder $dossier, User $destinataire, int $parentIdCopie, bool $verifierDoublon = false): ArchiveFolder
    {
        $nom = $verifierDoublon
            ? $this->nomDossierDisponiblePour($dossier->nom, $destinataire->id, $parentIdCopie)
            : $dossier->nom;

        $copieDossier = ArchiveFolder::create([
            'user_id' => $destinataire->id,
            'parent_id' => $parentIdCopie,
            'nom' => $nom,
        ]);

        foreach ($dossier->fichiers as $fichier) {
            $this->copierFichierPour($fichier, $destinataire, $copieDossier->id);
        }

        foreach ($dossier->children as $enfant) {
            $this->copierDossierPour($enfant, $destinataire, $copieDossier->id);
        }

        return $copieDossier;
    }

    private function nomDossierDisponiblePour(string $nomSouhaite, int $destinataireId, int $parentId): string
    {
        $nom = $nomSouhaite;
        $suffixe = 2;

        while (ArchiveFolder::where('user_id', $destinataireId)->where('parent_id', $parentId)->where('nom', $nom)->exists()) {
            $nom = $nomSouhaite.' ('.$suffixe.')';
            $suffixe++;
        }

        return $nom;
    }

    private function dossierPartagesRecusPour(User $destinataire): ArchiveFolder
    {
        return ArchiveFolder::firstOrCreate([
            'user_id' => $destinataire->id,
            'parent_id' => null,
            'nom' => 'Partages reçus',
        ]);
    }

    private function genererZipDossier(ArchiveFolder $dossierCopie): string
    {
        Storage::disk('public')->makeDirectory('archives/dossier-partages');
        $cheminZip = 'archives/dossier-partages/'.Str::random(40).'.zip';
        $cheminAbsolu = Storage::disk('public')->path($cheminZip);

        $zip = new ZipArchive;
        $zip->open($cheminAbsolu, ZipArchive::CREATE);
        $zip->addEmptyDir($dossierCopie->nom);
        $this->ajouterDossierAuZip($zip, $dossierCopie, $dossierCopie->nom.'/');
        $zip->close();

        return $cheminZip;
    }

    private function ajouterDossierAuZip(ZipArchive $zip, ArchiveFolder $dossier, string $prefixeZip): void
    {
        foreach ($dossier->fichiers as $fichier) {
            $contenu = Storage::disk('public')->get($fichier->chemin_fichier);
            $zip->addFromString($prefixeZip.$fichier->nom_fichier, $contenu);
        }

        foreach ($dossier->children as $enfant) {
            $zip->addEmptyDir($prefixeZip.$enfant->nom);
            $this->ajouterDossierAuZip($zip, $enfant, $prefixeZip.$enfant->nom.'/');
        }
    }
}
