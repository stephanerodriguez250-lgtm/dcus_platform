<?php

namespace App\Http\Controllers;

use App\Mail\ConvocationCodir;
use App\Models\Codir;
use App\Models\CodirAcces;
use App\Models\CodirParticipant;
use App\Models\CodirRapport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CodirController extends Controller
{
    // ── Liste des CODIR ──────────────────────────────────────────────
    public function index()
    {
        $codirs = Codir::with(['createur', 'participants', 'rapports'])
            ->orderByDesc('date')
            ->paginate(10);

        return view('codirs.index', compact('codirs'));
    }

    // ── Formulaire création ──────────────────────────────────────────
    public function create()
    {
        $this->authorizeManage();
        return view('codirs.create');
    }

    // ── Enregistrement ───────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'objet'             => 'required|string|max:255',
            'date'              => 'required|date',
            'heure_debut'       => 'nullable|date_format:H:i',
            'heure_fin'         => 'nullable|date_format:H:i',
            'lieu'              => 'nullable|string|max:255',
            'presidente'        => 'nullable|string|max:255',
            'rapporteur'        => 'nullable|string|max:255',
            'statut'            => 'required|in:planifie,tenu,annule',
            'prochaine_reunion' => 'nullable|date',

            // Participants dynamiques
            'participants'              => 'nullable|array',
            'participants.*.nom_complet'=> 'required|string|max:255',
            'participants.*.email'      => 'nullable|email|max:255',
            'participants.*.fonction'   => 'nullable|string|max:255',
            'participants.*.present'    => 'nullable',
        ]);

        $data['created_by'] = Auth::id();
        $participants = $data['participants'] ?? [];
        unset($data['participants']);

        $codir = Codir::create($data);

        // Enregistrer les participants
        foreach ($participants as $p) {
            $codir->participants()->create([
                'nom_complet' => $p['nom_complet'],
                'email'       => $p['email'] ?? null,
                'fonction'    => $p['fonction'] ?? null,
                'present'     => isset($p['present']) ? true : false,
            ]);
        }

        // Envoi emails aux participants ayant un email
        foreach ($codir->participants()->whereNotNull('email')->get() as $participant) {
            try {
                Mail::to($participant->email)
                    ->send(new ConvocationCodir($codir, $participant->nom_complet));
            } catch (\Exception $e) {
                // Continue si un email échoue
            }
        }

        // Aussi envoyer aux agents DCUS actifs
        foreach (User::where('actif', true)->get() as $agent) {
            try {
                Mail::to($agent->email)
                    ->send(new ConvocationCodir($codir, $agent->nom_complet));
            } catch (\Exception $e) {}
        }

        return redirect()->route('codirs.show', $codir)
            ->with('success', 'CODIR créé et notifications envoyées.');
    }

    // ── Détail d'un CODIR ────────────────────────────────────────────
    public function show(Codir $codir)
    {
        $codir->load(['createur', 'participants', 'rapports.uploadeur', 'acces.utilisateur']);
        $users = User::where('actif', true)->orderBy('nom')->get();
        $peutTelecharger = $codir->userPeutTelecharger(Auth::id())
                        || Auth::user()->isAdmin();

        return view('codirs.show', compact('codir', 'users', 'peutTelecharger'));
    }

    // ── Formulaire édition ───────────────────────────────────────────
    public function edit(Codir $codir)
    {
        $this->authorizeManage();
        return view('codirs.edit', compact('codir'));
    }

    // ── Mise à jour ──────────────────────────────────────────────────
    public function update(Request $request, Codir $codir)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'objet'             => 'required|string|max:255',
            'date'              => 'required|date',
            'heure_debut'       => 'nullable|date_format:H:i',
            'heure_fin'         => 'nullable|date_format:H:i',
            'lieu'              => 'nullable|string|max:255',
            'presidente'        => 'nullable|string|max:255',
            'rapporteur'        => 'nullable|string|max:255',
            'synthese'          => 'nullable|string',
            'decisions'         => 'nullable|string',
            'divers'            => 'nullable|string',
            'statut'            => 'required|in:planifie,tenu,annule',
            'prochaine_reunion' => 'nullable|date',
        ]);

        $codir->update($data);

        return redirect()->route('codirs.show', $codir)
            ->with('success', 'CODIR mis à jour.');
    }

    // ── Suppression ──────────────────────────────────────────────────
    public function destroy(Codir $codir)
    {
        $this->authorizeManage();

        // Supprimer les fichiers uploadés
        foreach ($codir->rapports as $rapport) {
            Storage::disk('public')->delete($rapport->chemin_fichier);
        }

        $codir->delete();
        return redirect()->route('codirs.index')
            ->with('success', 'CODIR supprimé.');
    }

    // ── Upload d'un rapport ──────────────────────────────────────────
    public function uploadRapport(Request $request, Codir $codir)
    {
        $this->authorizeAdmin();

        $request->validate([
            'rapport' => 'required|file|mimes:pdf,docx,doc|max:10240',
        ]);

        $file = $request->file('rapport');
        $path = $file->store('codirs/rapports', 'public');

        $codir->rapports()->create([
            'nom_fichier'   => $file->getClientOriginalName(),
            'chemin_fichier'=> $path,
            'type_fichier'  => $file->getClientOriginalExtension(),
            'taille'        => $file->getSize(),
            'uploaded_by'   => Auth::id(),
        ]);

        return back()->with('success', 'Rapport uploadé avec succès.');
    }

    // ── Téléchargement d'un rapport ──────────────────────────────────
    public function downloadRapport(Codir $codir, CodirRapport $rapport)
    {
        // Vérifier l'accès
        if (!Auth::user()->isAdmin() && !$codir->userPeutTelecharger(Auth::id())) {
            abort(403, 'Vous n\'avez pas accès à ce rapport.');
        }

        if (!Storage::disk('public')->exists($rapport->chemin_fichier)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        return Storage::disk('public')->download(
            $rapport->chemin_fichier,
            $rapport->nom_fichier
        );
    }

    // ── Supprimer un rapport ─────────────────────────────────────────
    public function deleteRapport(Codir $codir, CodirRapport $rapport)
    {
        $this->authorizeAdmin();
        Storage::disk('public')->delete($rapport->chemin_fichier);
        $rapport->delete();
        return back()->with('success', 'Rapport supprimé.');
    }

    // ── Gérer les accès au téléchargement ───────────────────────────
    public function gererAcces(Request $request, Codir $codir)
    {
        $this->authorizeAdmin();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'action'  => 'required|in:donner,retirer',
        ]);

        if ($request->action === 'donner') {
            // Éviter les doublons
            if (!$codir->userPeutTelecharger($request->user_id)) {
                CodirAcces::create([
                    'codir_id'    => $codir->id,
                    'user_id'     => $request->user_id,
                    'accorde_par' => Auth::id(),
                ]);
            }
            return back()->with('success', 'Accès accordé.');
        } else {
            $codir->acces()->where('user_id', $request->user_id)->delete();
            return back()->with('success', 'Accès retiré.');
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────
    private function authorizeManage()
    {
        if (!Auth::user()->canManage()) {
            abort(403);
        }
    }

    private function authorizeAdmin()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
