<?php

namespace App\Http\Controllers;

use App\Models\Accord;
use App\Models\AccordHistorique;
use App\Models\User;
use App\Notifications\AccordCreeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AccordController extends Controller
{
    // Liste des accords
    public function index(Request $request)
    {
        $query = Accord::with(['createur', 'appreciation'])->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('titre', 'like', '%'.$request->search.'%')
                    ->orWhere('reference', 'like', '%'.$request->search.'%')
                    ->orWhere('institution_partenaire', 'like', '%'.$request->search.'%');
            });
        }

        $accords = $query->paginate(10)->withQueryString();

        $etapeCounts = [
            'recu' => Accord::doesntHave('appreciation')->count(),
            'apprecie' => Accord::has('appreciation')->whereNull('envoye_le')->count(),
            'envoye' => Accord::whereNotNull('envoye_le')->whereNull('date_signature')->count(),
            'signe' => Accord::whereNotNull('date_signature')->count(),
        ];

        return view('accords.index', compact('accords', 'etapeCounts'));
    }

    // Formulaire de création
    public function create()
    {
        $this->authorize('create', Accord::class);

        return view('accords.create');
    }

    // Enregistrement (étape 1 : réception)
    public function store(Request $request)
    {
        $this->authorize('create', Accord::class);

        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'institution_partenaire' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'date_arrivee' => 'nullable|date',
            'heure_arrivee' => 'nullable|date_format:H:i',
            'fichier' => 'required|file|mimes:pdf,doc,docx|max:20480',
        ]);

        $data['date_arrivee'] = $data['date_arrivee'] ?? now()->toDateString();
        $data['heure_arrivee'] = $data['heure_arrivee'] ?? now()->format('H:i');

        $file = $request->file('fichier');
        $data['chemin_fichier'] = $file->store('accords/fichiers', 'public');
        $data['nom_fichier'] = $file->getClientOriginalName();
        unset($data['fichier']);

        $data['created_by'] = Auth::id();
        $accord = Accord::create($data);

        AccordHistorique::create([
            'accord_id' => $accord->id,
            'evenement' => 'Accord reçu',
            'modifie_par' => Auth::id(),
            'date_modification' => now(),
        ]);

        foreach (User::actifsSauf(Auth::id()) as $utilisateur) {
            $utilisateur->notify(new AccordCreeNotification($accord, Auth::user()));
        }

        return redirect()->route('accords.show', $accord)
            ->with('success', 'Accord enregistré avec succès.');
    }

    // Détail d'un accord
    public function show(Accord $accord)
    {
        $accord->load(['createur', 'appreciation.redacteur', 'historiques.modificateur']);

        return view('accords.show', compact('accord'));
    }

    // Formulaire d'édition
    public function edit(Accord $accord)
    {
        $this->authorize('update', $accord);

        return view('accords.edit', compact('accord'));
    }

    // Mise à jour (étape 1, corrections)
    public function update(Request $request, Accord $accord)
    {
        $this->authorize('update', $accord);

        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'institution_partenaire' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'date_arrivee' => 'required|date',
            'heure_arrivee' => 'required|date_format:H:i',
            'fichier' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
        ]);

        if ($request->hasFile('fichier')) {
            if ($accord->chemin_fichier) {
                Storage::disk('public')->delete($accord->chemin_fichier);
            }
            $file = $request->file('fichier');
            $data['chemin_fichier'] = $file->store('accords/fichiers', 'public');
            $data['nom_fichier'] = $file->getClientOriginalName();
        }
        unset($data['fichier']);

        $accord->update($data);

        return redirect()->route('accords.show', $accord)
            ->with('success', 'Accord mis à jour avec succès.');
    }

    // Étape 3a : marquer l'accord + la fiche comme envoyés au destinataire
    public function envoyer(Accord $accord)
    {
        $this->authorize('update', $accord);

        if ($accord->envoye_le) {
            return back()->with('error', 'Cet accord a déjà été marqué comme envoyé.');
        }

        $accord->update(['envoye_le' => now()->toDateString()]);

        AccordHistorique::create([
            'accord_id' => $accord->id,
            'evenement' => 'Accord et fiche d\'appréciation envoyés au destinataire',
            'modifie_par' => Auth::id(),
            'date_modification' => now(),
        ]);

        return back()->with('success', 'Accord marqué comme envoyé.');
    }

    // Étape 3b : enregistrer la signature
    public function signer(Request $request, Accord $accord)
    {
        $this->authorize('update', $accord);

        $data = $request->validate([
            'date_signature' => 'nullable|date',
            'duree_valeur' => 'required|integer|min:1',
            'duree_unite' => 'required|in:mois,ans',
        ]);

        $accord->date_signature = $data['date_signature'] ?? now()->toDateString();
        $accord->duree_valeur = $data['duree_valeur'];
        $accord->duree_unite = $data['duree_unite'];
        $accord->date_expiration = $accord->calculerDateExpiration();
        $accord->save();

        AccordHistorique::create([
            'accord_id' => $accord->id,
            'evenement' => 'Signature enregistrée',
            'modifie_par' => Auth::id(),
            'date_modification' => now(),
        ]);

        return redirect()->route('accords.show', $accord)->with('success', 'Signature enregistrée.');
    }

    // Suppression
    public function destroy(Accord $accord)
    {
        $this->authorize('delete', $accord);

        if ($accord->chemin_fichier) {
            Storage::disk('public')->delete($accord->chemin_fichier);
        }
        if ($accord->appreciation?->chemin_fiche_word) {
            Storage::disk('public')->delete($accord->appreciation->chemin_fiche_word);
        }

        $accord->delete();

        return redirect()->route('accords.index')
            ->with('success', 'Accord supprimé.');
    }
}
