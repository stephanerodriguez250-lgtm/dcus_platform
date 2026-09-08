<?php

namespace App\Http\Controllers;

use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
use App\Models\User;
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

        return redirect()->route('archives.index', $parent ? ['dossier' => $parent->id] : [])
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

        return redirect()->route('archives.index', $dossier->parent_id ? ['dossier' => $dossier->parent_id] : [])
            ->with('success', 'Dossier renommé.');
    }

    public function destroyDossier(ArchiveFolder $dossier)
    {
        $this->authorize('delete', $dossier);

        foreach ($dossier->fichiersRecursifs() as $fichier) {
            Storage::disk('public')->delete($fichier->chemin_fichier);
        }

        $parentId = $dossier->parent_id;
        $dossier->delete();

        return redirect()->route('archives.index', $parentId ? ['dossier' => $parentId] : [])
            ->with('success', 'Dossier supprimé.');
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
        $dossier = null;
        if ($request->query('dossier')) {
            $dossier = ArchiveFolder::findOrFail($request->query('dossier'));
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
            'fichier' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
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

        return redirect()->route('archives.index', $dossier ? ['dossier' => $dossier->id] : [])
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

        return redirect()->route('archives.index', $fichier->folder_id ? ['dossier' => $fichier->folder_id] : [])
            ->with('success', 'Fichier modifié.');
    }

    public function destroyFichier(ArchiveFichier $fichier)
    {
        $this->authorize('delete', $fichier);

        Storage::disk('public')->delete($fichier->chemin_fichier);
        $folderId = $fichier->folder_id;
        $fichier->delete();

        return redirect()->route('archives.index', $folderId ? ['dossier' => $folderId] : [])
            ->with('success', 'Fichier supprimé.');
    }

    public function downloadFichier(ArchiveFichier $fichier)
    {
        $this->authorize('view', $fichier);

        if (! Storage::disk('public')->exists($fichier->chemin_fichier)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        return Storage::disk('public')->download($fichier->chemin_fichier, $fichier->nom_fichier);
    }
}
