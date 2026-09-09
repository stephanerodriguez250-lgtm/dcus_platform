<?php

namespace App\Http\Controllers;

use App\Models\Accord;
use App\Models\AccordHistorique;
use App\Models\AccordRapportConformite;
use App\Services\AccordConformiteAnalyzer;
use App\Services\RapportConformiteGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AccordConformiteController extends Controller
{
    /**
     * Rôle 2 de l'agent IA : compare le document signé aux observations de la fiche
     * d'appréciation, puis génère le rapport de conformité (.docx).
     */
    public function analyser(Accord $accord, AccordConformiteAnalyzer $analyzer, RapportConformiteGenerator $generator)
    {
        $this->authorize('update', $accord);

        try {
            $resultat = $analyzer->analyser($accord);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $ancienChemin = $accord->rapportConformite?->chemin_rapport_word;

        $rapport = $accord->rapportConformite()->updateOrCreate([], [
            'resume' => $resultat['resume'] ?? '',
            'points_conformes' => $resultat['points_conformes'] ?? null,
            'points_non_conformes' => $resultat['points_non_conformes'] ?? null,
            'genere_par' => Auth::id(),
            'genere_le' => now(),
        ]);

        $chemin = $generator->generer($rapport);
        $rapport->update(['chemin_rapport_word' => $chemin]);

        if ($ancienChemin && $ancienChemin !== $chemin) {
            Storage::disk('public')->delete($ancienChemin);
        }

        AccordHistorique::create([
            'accord_id' => $accord->id,
            'evenement' => 'Analyse de conformité générée par '.Auth::user()->nom_complet,
            'modifie_par' => Auth::id(),
            'date_modification' => now(),
        ]);

        return redirect()->route('accords.show', $accord)->with('success', 'Analyse de conformité générée.');
    }

    public function telecharger(AccordRapportConformite $rapport)
    {
        $this->authorize('update', $rapport->accord);

        if (! $rapport->chemin_rapport_word || ! Storage::disk('public')->exists($rapport->chemin_rapport_word)) {
            return back()->with('error', 'Rapport introuvable.');
        }

        $referenceSure = preg_replace('/[\/\\\\]+/', '-', $rapport->accord->reference);
        $nom = 'rapport-conformite-'.$referenceSure.'.docx';

        return Storage::disk('public')->download($rapport->chemin_rapport_word, $nom);
    }
}
