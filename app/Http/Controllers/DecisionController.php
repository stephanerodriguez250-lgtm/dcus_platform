<?php

namespace App\Http\Controllers;

use App\Models\Decision;
use App\Models\DecisionHistorique;
use App\Models\User;
use App\Notifications\DecisionCreeeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DecisionController extends Controller
{
    // ── Liste globale des décisions ──────────────────────────────────
    public function index(Request $request)
    {
        $query = Decision::with(['codir', 'reunion', 'createur'])
            ->orderByDesc('created_at');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('source')) {
            $source = $request->source;
            if ($source === 'note_ministerielle') {
                $query->where('source_type', 'note_ministerielle');
            } elseif (in_array($source, array_keys(Decision::$sourceTypeLabels))) {
                $query->where('source_type', $source);
            } elseif ($source === 'codir') {
                $query->whereIn('source_type', ['codir_interne', 'codir_externe']);
            } elseif ($source === 'reunion') {
                $query->whereIn('source_type', ['reunion_interne', 'reunion_externe']);
            }
        }

        if ($request->filled('search')) {
            $query->where('intitule', 'like', '%'.$request->search.'%');
        }

        $decisions = $query->paginate(15)->withQueryString();
        $statuts = Decision::$statuts;

        // CORRECTION 1 : return était sorti de la fonction index() par erreur
        return view('decisions.index', compact('decisions', 'statuts'));
    }

    // ── Créer une décision ───────────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorize('create', Decision::class);

        $data = $request->validate([
            'intitule' => 'required|string|max:500',
            'responsable' => 'nullable|string|max:255',
            'echeance' => 'nullable|date',
            'statut' => 'required|in:'.implode(',', array_keys(Decision::$statuts)),
            'progression' => 'required|integer|min:0|max:100',
            'commentaire' => 'nullable|string',
            'source_type' => 'required|in:'.implode(',', array_keys(Decision::$sourceTypeLabels)),
            'source_id' => 'nullable|string',
            'note_numero' => 'nullable|string|max:100',
            'note_date' => 'nullable|date',
            'note_expediteur' => 'nullable|string|max:255',
            'note_objet' => 'nullable|string|max:500',
            'note_fichier' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $data['codir_id'] = null;
        $data['reunion_id'] = null;

        if (! empty($request->source_id)) {
            [$type, $id] = explode('_', $request->source_id, 2);

            if ($type === 'codir' && is_numeric($id)) {
                if (in_array($data['source_type'], ['codir_interne', 'codir_externe'])) {
                    $data['codir_id'] = (int) $id;
                }
            } elseif ($type === 'reunion' && is_numeric($id)) {
                if (in_array($data['source_type'], ['reunion_interne', 'reunion_externe'])) {
                    $data['reunion_id'] = (int) $id;
                }
            }
        }

        if ($request->hasFile('note_fichier') && $data['source_type'] === 'note_ministerielle') {
            $data['note_fichier'] = $request->file('note_fichier')
                ->store('notes_ministerielles', 'public');
        } else {
            unset($data['note_fichier']);
        }

        unset($data['source_id']);

        $data['created_by'] = Auth::id();

        $decision = Decision::create($data);

        DecisionHistorique::create([
            'decision_id' => $decision->id,
            'ancien_statut' => null,
            'nouveau_statut' => $decision->statut,
            'progression' => $decision->progression,
            'commentaire' => 'Décision créée.',
            'modifie_par' => Auth::id(),
            'date_modification' => now(),
        ]);

        foreach (User::actifsSauf(Auth::id()) as $utilisateur) {
            $utilisateur->notify(new DecisionCreeeNotification($decision, Auth::user()));
        }

        return back()->with('success', 'Décision enregistrée.');
    }

    // ── Détail d'une décision ────────────────────────────────────────
    public function show(Decision $decision)
    {
        $decision->load(['codir', 'reunion', 'createur',
            'historiques.modificateur']);

        return view('decisions.show', compact('decision'));
    }

    // ── Mise à jour du statut et progression ─────────────────────────
    public function update(Request $request, Decision $decision)
    {
        $this->authorize('update', $decision);

        $data = $request->validate([
            'statut' => 'required|in:'.implode(',', array_keys(Decision::$statuts)),
            'progression' => 'required|integer|min:0|max:100',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        $ancienStatut = $decision->statut;

        $decision->update($data);

        DecisionHistorique::create([
            'decision_id' => $decision->id,
            'ancien_statut' => $ancienStatut,
            'nouveau_statut' => $data['statut'],
            'progression' => $data['progression'],
            'commentaire' => $data['commentaire'] ?? null,
            'modifie_par' => Auth::id(),
            'date_modification' => now(),
        ]);

        return back()->with('success', 'Décision mise à jour.');
    }

    // ── Suppression ──────────────────────────────────────────────────
    public function destroy(Decision $decision)
    {
        $this->authorize('delete', $decision);

        if ($decision->note_fichier) {
            Storage::disk('public')->delete($decision->note_fichier);
        }

        $decision->delete();

        return back()->with('success', 'Décision supprimée.');
    }

    // ── Télécharger la pièce jointe d'une note ministérielle ─────────
    public function downloadNote(Decision $decision)
    {
        if (! $decision->note_fichier || ! Storage::disk('public')->exists($decision->note_fichier)) {
            abort(404);
        }

        return Storage::disk('public')->download($decision->note_fichier);
    }
}
