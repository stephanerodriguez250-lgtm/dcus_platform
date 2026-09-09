<?php

namespace App\Http\Controllers;

use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
use App\Models\User;
use App\Support\IdHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    public function index(?ArchiveFolder $dossier = null)
    {
        if ($dossier) {
            $this->authorize('view', $dossier);
        }

        $sousDossiers = $dossier
            ? $dossier->children()->orderBy('nom')->get()
            : ArchiveFolder::where('user_id', Auth::id())->whereNull('parent_id')->orderBy('nom')->get();

        $fichiers = $dossier
            ? $dossier->fichiers()->latest()->get()
            : ArchiveFichier::where('user_id', Auth::id())->whereNull('folder_id')->latest()->get();

        $filAriane = $dossier ? $dossier->filAriane() : [];

        $autresUtilisateurs = User::where('id', '!=', Auth::id())->where('actif', true)->orderBy('nom')->get();

        return view('archives.index', compact('dossier', 'sousDossiers', 'fichiers', 'filAriane', 'autresUtilisateurs'));
    }

    public function storeDossier(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:archive_folders,id',
        ]);

        $parent = null;
        if ($validated['parent_id'] ?? null) {
            $parent = ArchiveFolder::findOrFail($validated['parent_id']);
            $this->authorize('view', $parent);
        }

        if ($this->nomDejaPris($validated['nom'], $parent?->id)) {
            return back()->withErrors(['nom' => 'Un dossier porte déjà ce nom à cet emplacement.'])->withInput();
        }

        ArchiveFolder::create([
            'user_id' => Auth::id(),
            'parent_id' => $parent?->id,
            'nom' => $validated['nom'],
        ]);

        return redirect()->route('archives.index', $parent ? ['dossier' => $parent] : [])
            ->with('success', 'Dossier créé.');
    }

    public function updateDossier(Request $request, ArchiveFolder $dossier)
    {
        $this->authorize('update', $dossier);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        if ($validated['nom'] !== $dossier->nom && $this->nomDejaPris($validated['nom'], $dossier->parent_id, $dossier->id)) {
            return back()->withErrors(['nom' => 'Un dossier porte déjà ce nom à cet emplacement.'])->withInput();
        }

        $dossier->update(['nom' => $validated['nom']]);

        return redirect()->route('archives.index', $dossier->parent ? ['dossier' => $dossier->parent] : [])
            ->with('success', 'Dossier renommé.');
    }

    public function destroyDossier(ArchiveFolder $dossier)
    {
        $this->authorize('delete', $dossier);

        foreach ($dossier->fichiersRecursifs() as $fichier) {
            Storage::disk('public')->delete($fichier->chemin_fichier);
        }

        $parent = $dossier->parent;
        $dossier->delete();

        return redirect()->route('archives.index', $parent ? ['dossier' => $parent] : [])
            ->with('success', 'Dossier supprimé.');
    }

    public function deplacerDossier(Request $request, ArchiveFolder $dossier)
    {
        $this->authorize('update', $dossier);

        // Envoyé par le JS de glisser-déposer avec l'ID haché du dossier cible (voir
        // data-dossier-id dans archives/index.blade.php) — pas un ID brut, donc pas de règle
        // "exists:...,id" possible ici : on décode nous-mêmes avant de chercher le dossier.
        $validated = $request->validate([
            'parent_id' => 'nullable|string',
        ]);

        $dossierCible = null;
        if ($validated['parent_id'] ?? null) {
            $idCible = IdHasher::decoder($validated['parent_id']);
            abort_if($idCible === null, 404);
            $dossierCible = ArchiveFolder::findOrFail($idCible);
            $this->authorize('view', $dossierCible);

            if ($dossierCible->id === $dossier->id || $dossier->descendantsRecursifs()->contains('id', $dossierCible->id)) {
                return back()->with('error', 'Impossible de déplacer un dossier dans lui-même ou l\'un de ses sous-dossiers.');
            }
        }

        if ($this->nomDejaPris($dossier->nom, $dossierCible?->id, $dossier->id)) {
            return back()->with('error', 'Un dossier porte déjà ce nom à cet emplacement.');
        }

        $ancienParent = $dossier->parent;
        $dossier->update(['parent_id' => $dossierCible?->id]);

        return redirect()->route('archives.index', $ancienParent ? ['dossier' => $ancienParent] : [])
            ->with('success', 'Dossier déplacé.');
    }

    private function nomDejaPris(string $nom, ?int $parentId, ?int $ignorerId = null): bool
    {
        $query = ArchiveFolder::where('user_id', Auth::id())->where('nom', $nom);
        $parentId ? $query->where('parent_id', $parentId) : $query->whereNull('parent_id');
        if ($ignorerId) {
            $query->where('id', '!=', $ignorerId);
        }

        return $query->exists();
    }

    public function createFichier(Request $request)
    {
        // Le "?dossier=" ici est un simple paramètre de requête (pas de segment {dossier}
        // dans cette route), donc le binding implicite de route ne s'applique pas : il faut
        // décoder le hash nous-mêmes, comme le fait resolveRouteBinding() pour les autres routes.
        $dossier = null;
        if ($request->query('dossier')) {
            $id = IdHasher::decoder((string) $request->query('dossier'));
            abort_if($id === null, 404);
            $dossier = ArchiveFolder::findOrFail($id);
            $this->authorize('view', $dossier);
        }

        return view('archives.fichiers.create', compact('dossier'));
    }

    public function storeFichier(Request $request)
    {
        $validated = $request->validate([
            'dossier_id' => 'nullable|exists:archive_folders,id',
            'intitule' => 'required|string|max:255',
            'numero' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'fichier' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:102400',
        ]);

        $dossier = null;
        if ($validated['dossier_id'] ?? null) {
            $dossier = ArchiveFolder::findOrFail($validated['dossier_id']);
            $this->authorize('view', $dossier);
        }

        $file = $request->file('fichier');
        $path = $file->store('archives/fichiers', 'public');

        ArchiveFichier::create([
            'user_id' => Auth::id(),
            'folder_id' => $dossier?->id,
            'intitule' => $validated['intitule'],
            'numero' => $validated['numero'] ?? null,
            'description' => $validated['description'] ?? null,
            'nom_fichier' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'type_fichier' => $file->getClientOriginalExtension(),
            'taille' => $file->getSize(),
        ]);

        return redirect()->route('archives.index', $dossier ? ['dossier' => $dossier] : [])
            ->with('success', 'Fichier ajouté.');
    }

    public function updateFichier(Request $request, ArchiveFichier $fichier)
    {
        $this->authorize('update', $fichier);

        $validated = $request->validate([
            'intitule' => 'required|string|max:255',
            'numero' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $fichier->update($validated);

        return redirect()->route('archives.index', $fichier->dossier ? ['dossier' => $fichier->dossier] : [])
            ->with('success', 'Fichier modifié.');
    }

    public function destroyFichier(ArchiveFichier $fichier)
    {
        $this->authorize('delete', $fichier);

        Storage::disk('public')->delete($fichier->chemin_fichier);
        $dossier = $fichier->dossier;
        $fichier->delete();

        return redirect()->route('archives.index', $dossier ? ['dossier' => $dossier] : [])
            ->with('success', 'Fichier supprimé.');
    }

    public function deplacerFichier(Request $request, ArchiveFichier $fichier)
    {
        $this->authorize('update', $fichier);

        // Même chose que deplacerDossier() : un ID de dossier haché, pas brut.
        $validated = $request->validate([
            'dossier_id' => 'nullable|string',
        ]);

        $dossierCible = null;
        if ($validated['dossier_id'] ?? null) {
            $idCible = IdHasher::decoder($validated['dossier_id']);
            abort_if($idCible === null, 404);
            $dossierCible = ArchiveFolder::findOrFail($idCible);
            $this->authorize('view', $dossierCible);
        }

        $ancienDossier = $fichier->dossier;
        $fichier->update(['folder_id' => $dossierCible?->id]);

        return redirect()->route('archives.index', $ancienDossier ? ['dossier' => $ancienDossier] : [])
            ->with('success', 'Fichier déplacé.');
    }

    public function downloadFichier(ArchiveFichier $fichier)
    {
        $this->authorize('view', $fichier);

        if (! Storage::disk('public')->exists($fichier->chemin_fichier)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        return Storage::disk('public')->download($fichier->chemin_fichier, $fichier->nom_fichier);
    }

    private static array $typesApercuSurs = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
    ];

    public function apercuFichier(ArchiveFichier $fichier)
    {
        $this->authorize('view', $fichier);

        if (! Storage::disk('public')->exists($fichier->chemin_fichier)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        $mimeSur = self::$typesApercuSurs[strtolower($fichier->type_fichier)] ?? null;

        if (! $mimeSur) {
            return Storage::disk('public')->download($fichier->chemin_fichier, $fichier->nom_fichier);
        }

        return Storage::disk('public')->response($fichier->chemin_fichier, $fichier->nom_fichier, [
            'Content-Type' => $mimeSur,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }
}
