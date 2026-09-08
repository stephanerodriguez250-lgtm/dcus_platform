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
}
