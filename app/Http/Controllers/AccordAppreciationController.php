<?php

namespace App\Http\Controllers;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use App\Models\AccordHistorique;
use App\Services\FicheAppreciationGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AccordAppreciationController extends Controller
{
    public function create(Accord $accord)
    {
        $this->authorize('apprecier', Accord::class);
        $accord->load('appreciation');

        return view('accords.appreciation', compact('accord'));
    }

    public function store(Request $request, Accord $accord, FicheAppreciationGenerator $generator)
    {
        $this->authorize('apprecier', Accord::class);

        $data = $request->validate([
            'origine' => 'required|string|max:255',
            'objet' => 'required|string',
            'avis' => 'required|string',
            'observations_forme' => 'nullable|string',
            'observations_fond' => 'nullable|string',
        ]);

        $appreciation = $accord->appreciation;
        $ancienChemin = $appreciation?->chemin_fiche_word;

        $data['redige_par'] = Auth::id();
        $appreciation = $accord->appreciation()->updateOrCreate([], $data);

        $chemin = $generator->generer($appreciation);
        $appreciation->update(['chemin_fiche_word' => $chemin]);

        if ($ancienChemin && $ancienChemin !== $chemin) {
            Storage::disk('public')->delete($ancienChemin);
        }

        AccordHistorique::create([
            'accord_id' => $accord->id,
            'evenement' => 'Appréciation enregistrée par '.Auth::user()->nom_complet,
            'modifie_par' => Auth::id(),
            'date_modification' => now(),
        ]);

        return redirect()->route('accords.show', $accord)->with('success', 'Appréciation enregistrée.');
    }

    public function telecharger(AccordAppreciation $appreciation)
    {
        if (! $appreciation->chemin_fiche_word || ! Storage::disk('public')->exists($appreciation->chemin_fiche_word)) {
            return back()->with('error', 'Fiche introuvable.');
        }

        $referenceSure = preg_replace('/[\/\\\\]+/', '-', $appreciation->accord->reference);
        $nom = 'fiche-appreciation-'.$referenceSure.'.docx';

        return Storage::disk('public')->download($appreciation->chemin_fiche_word, $nom);
    }
}
