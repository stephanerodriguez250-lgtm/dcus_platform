<?php

namespace App\Http\Controllers;

use App\Models\Accord;
use App\Models\AccordHistorique;
use App\Models\Reunion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccordController extends Controller
{
    // Liste des accords
    public function index(Request $request)
    {
        $query = Accord::with(['reunion', 'createur'])->orderByDesc('created_at');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('titre', 'like', '%' . $request->search . '%')
                  ->orWhere('institution_partenaire', 'like', '%' . $request->search . '%')
                  ->orWhere('pays_partenaire', 'like', '%' . $request->search . '%')
                  ->orWhere('universite_beneficiaire', 'like', '%' . $request->search . '%');
            });
        }

        $accords = $query->paginate(10)->withQueryString();
        $statuts  = Accord::$statuts;

        return view('accords.index', compact('accords', 'statuts'));
    }

    // Formulaire de création
    public function create(Request $request)
    {
        $this->authorizeManage();
        $statuts  = Accord::$statuts;
        $reunions = Reunion::orderByDesc('date')->get();
        $reunion_id = $request->get('reunion_id');
        return view('accords.create', compact('statuts', 'reunions', 'reunion_id'));
    }

    // Enregistrement
    public function store(Request $request)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'titre'                   => 'required|string|max:255',
            'institution_partenaire'  => 'required|string|max:255',
            'pays_partenaire'         => 'required|string|max:255',
            'universite_beneficiaire' => 'nullable|string|max:255',
            'description'             => 'nullable|string',
            'date_identification'     => 'nullable|date',
            'date_signature'          => 'nullable|date',
            'date_expiration'         => 'nullable|date|after_or_equal:date_signature',
            'statut'                  => 'required|in:' . implode(',', array_keys(Accord::$statuts)),
            'reunion_id'              => 'nullable|exists:reunions,id',
        ]);

        $data['created_by'] = Auth::id();
        $accord = Accord::create($data);

        // Enregistrer dans l'historique
        AccordHistorique::create([
            'accord_id'        => $accord->id,
            'ancien_statut'    => null,
            'nouveau_statut'   => $accord->statut,
            'commentaire'      => 'Accord créé.',
            'modifie_par'      => Auth::id(),
            'date_modification' => now(),
        ]);

        return redirect()->route('accords.show', $accord)
            ->with('success', 'Accord enregistré avec succès.');
    }

    // Détail d'un accord
    public function show(Accord $accord)
    {
        $accord->load(['reunion', 'createur', 'historiques.modificateur']);
        return view('accords.show', compact('accord'));
    }

    // Formulaire d'édition
    public function edit(Accord $accord)
    {
        $this->authorizeManage();
        $statuts  = Accord::$statuts;
        $reunions = Reunion::orderByDesc('date')->get();
        return view('accords.edit', compact('accord', 'statuts', 'reunions'));
    }

    // Mise à jour
    public function update(Request $request, Accord $accord)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'titre'                   => 'required|string|max:255',
            'institution_partenaire'  => 'required|string|max:255',
            'pays_partenaire'         => 'required|string|max:255',
            'universite_beneficiaire' => 'nullable|string|max:255',
            'description'             => 'nullable|string',
            'date_identification'     => 'nullable|date',
            'date_signature'          => 'nullable|date',
            'date_expiration'         => 'nullable|date',
            'reunion_id'              => 'nullable|exists:reunions,id',
        ]);

        $accord->update($data);

        return redirect()->route('accords.show', $accord)
            ->with('success', 'Accord mis à jour avec succès.');
    }

    // Mise à jour du statut uniquement
    public function updateStatut(Request $request, Accord $accord)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'statut'      => 'required|in:' . implode(',', array_keys(Accord::$statuts)),
            'commentaire' => 'nullable|string|max:500',
        ]);

        $ancienStatut = $accord->statut;

        if ($ancienStatut === $data['statut']) {
            return back()->with('error', 'Le statut est déjà identique.');
        }

        // Mise à jour dates automatiques
        if ($data['statut'] === 'signe' && !$accord->date_signature) {
            $accord->date_signature = today();
        }

        $accord->statut = $data['statut'];
        $accord->save();

        AccordHistorique::create([
            'accord_id'         => $accord->id,
            'ancien_statut'     => $ancienStatut,
            'nouveau_statut'    => $data['statut'],
            'commentaire'       => $data['commentaire'] ?? null,
            'modifie_par'       => Auth::id(),
            'date_modification' => now(),
        ]);

        return back()->with('success', 'Statut de l\'accord mis à jour.');
    }

    // Suppression
    public function destroy(Accord $accord)
    {
        $this->authorizeManage();
        $accord->delete();
        return redirect()->route('accords.index')
            ->with('success', 'Accord supprimé.');
    }

    private function authorizeManage()
    {
        if (!Auth::user()->canManage()) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
