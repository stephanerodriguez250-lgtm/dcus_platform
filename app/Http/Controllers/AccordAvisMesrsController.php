<?php

namespace App\Http\Controllers;

use App\Models\Accord;
use App\Models\AccordHistorique;
use App\Services\AccordAvisMesrsFusionGenerator;
use App\Services\FicheAppreciationGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AccordAvisMesrsController extends Controller
{
    public function create(Accord $accord)
    {
        $this->authorize('apprecier', Accord::class);

        if (! $accord->appreciation) {
            return redirect()->route('accords.show', $accord)
                ->with('error', "Vous devez d'abord enregistrer une appréciation avant de charger l'avis du MESRS.");
        }

        $accord->load('appreciation');

        return view('accords.avis-mesrs', compact('accord'));
    }

    /**
     * Rôle 3 de l'agent IA : lit la fiche scannée du ministère et la fusionne avec les
     * observations déjà rédigées par la DCUS. Le fichier est stocké immédiatement (nécessaire
     * pour l'appel Gemini) ; rien n'est encore enregistré sur la fiche — l'agent relit et
     * valide via store() avant que la fusion ne remplace le contenu existant.
     */
    public function suggerer(Request $request, Accord $accord, AccordAvisMesrsFusionGenerator $generator)
    {
        $this->authorize('apprecier', Accord::class);

        if (! $accord->appreciation) {
            return response()->json(['error' => "Vous devez d'abord enregistrer une appréciation avant de charger l'avis du MESRS."], 422);
        }

        $request->validate([
            'fichier_ministere' => 'required|file|mimes:pdf,png,jpg,jpeg|max:20480',
        ]);

        $fichier = $request->file('fichier_ministere');
        $chemin = $fichier->store('accords/avis-mesrs', 'public');

        try {
            $resultat = $generator->fusionner($accord, $chemin);
        } catch (Throwable $e) {
            Storage::disk('public')->delete($chemin);

            return response()->json(['error' => 'La fusion IA a échoué : '.$e->getMessage()], 422);
        }

        return response()->json([
            'chemin_fiche_ministere' => $chemin,
            'nom_fiche_ministere' => $fichier->getClientOriginalName(),
            'observations_forme' => $resultat['observations_forme'] ?? '',
            'observations_fond' => $resultat['observations_fond'] ?? '',
        ]);
    }

    public function store(Request $request, Accord $accord, FicheAppreciationGenerator $generator)
    {
        $this->authorize('apprecier', Accord::class);

        $appreciation = $accord->appreciation;

        if (! $appreciation) {
            return back()->with('error', "Vous devez d'abord enregistrer une appréciation avant de charger l'avis du MESRS.");
        }

        $data = $request->validate([
            // Facultatif : l'agent peut valider l'avis MESRS sans jamais charger de scan, en
            // saisissant lui-même les observations du ministère. Quand un fichier est fourni,
            // restreint volontairement au dossier alimenté par suggerer() : sans cette
            // contrainte, un agent pourrait faire pointer ce champ (une simple valeur postée)
            // vers n'importe quel fichier existant du disque public (document signé d'un autre
            // accord, archive d'un autre utilisateur...), qui serait ensuite supprimé par la
            // purge de l'"ancien" fichier lors d'une resoumission ultérieure.
            'chemin_fiche_ministere' => ['nullable', 'string', 'regex:/^accords\/avis-mesrs\/[A-Za-z0-9._-]+$/'],
            'nom_fiche_ministere' => 'nullable|string|max:255',
            'observations_forme' => 'nullable|string',
            'observations_fond' => 'nullable|string',
        ]);

        $cheminFicheMinistere = $data['chemin_fiche_ministere'] ?? null;
        $nomFicheMinistere = $data['nom_fiche_ministere'] ?? null;

        if ($cheminFicheMinistere && ! Storage::disk('public')->exists($cheminFicheMinistere)) {
            return back()->withErrors([
                'chemin_fiche_ministere' => "Le fichier analysé est introuvable, veuillez relancer l'analyse IA.",
            ]);
        }

        $ancienFichierMinistere = $appreciation->chemin_fiche_ministere;
        $ancienneFicheWord = $appreciation->chemin_fiche_word;

        $appreciation->update([
            'chemin_fiche_ministere' => $cheminFicheMinistere,
            'nom_fiche_ministere' => $nomFicheMinistere,
            'observations_forme' => $data['observations_forme'] ?? null,
            'observations_fond' => $data['observations_fond'] ?? null,
            'avis_mesrs_valide_le' => now(),
            'avis_mesrs_valide_par' => Auth::id(),
        ]);

        $chemin = $generator->generer($appreciation);
        $appreciation->update(['chemin_fiche_word' => $chemin]);

        if ($ancienneFicheWord && $ancienneFicheWord !== $chemin) {
            Storage::disk('public')->delete($ancienneFicheWord);
        }
        if ($ancienFichierMinistere && $ancienFichierMinistere !== $cheminFicheMinistere) {
            Storage::disk('public')->delete($ancienFichierMinistere);
        }

        AccordHistorique::create([
            'accord_id' => $accord->id,
            'evenement' => 'Avis MESRS fusionné et validé par '.Auth::user()->nom_complet,
            'modifie_par' => Auth::id(),
            'date_modification' => now(),
        ]);

        return redirect()->route('accords.show', $accord)->with('success', 'Avis MESRS enregistré et fusionné à la fiche.');
    }
}
