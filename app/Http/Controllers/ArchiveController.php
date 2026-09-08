<?php

namespace App\Http\Controllers;

use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
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

        return view('archives.index', compact('dossier', 'sousDossiers', 'fichiers', 'filAriane'));
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
}
