<?php

namespace App\Http\Controllers;

use App\Models\Accord;
use App\Models\AccordHistorique;
use App\Models\Reunion;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'reunions_total'      => Reunion::count(),
            'reunions_planifiees' => Reunion::where('statut', 'planifiee')->count(),
            'reunions_terminees'  => Reunion::where('statut', 'terminee')->count(),
            'accords_total'       => Accord::count(),
            'accords_en_cours'    => Accord::whereIn('statut', ['identifie', 'en_negotiation', 'en_execution'])->count(),
            'accords_signes'      => Accord::where('statut', 'signe')->count(),
            'accords_clotures'    => Accord::where('statut', 'cloture')->count(),
            'users_total'         => User::where('actif', true)->count(),
        ];

        $accords_par_statut = [];
        foreach (Accord::$statuts as $key => $label) {
            $accords_par_statut[$label] = Accord::where('statut', $key)->count();
        }

        $accords_par_pays = Accord::selectRaw('pays_partenaire, count(*) as total')
            ->groupBy('pays_partenaire')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'pays_partenaire');

        $prochaines_reunions = Reunion::where('date', '>=', today())
            ->where('statut', 'planifiee')
            ->orderBy('date')
            ->limit(5)
            ->get();

        $accords_recents = Accord::with('reunion')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $dernieres_activites = AccordHistorique::with(['accord', 'modificateur'])
            ->orderByDesc('date_modification')
            ->limit(6)
            ->get();

        $accords_expirants = Accord::whereNotNull('date_expiration')
            ->whereNotIn('statut', ['cloture', 'abandonne'])
            ->whereBetween('date_expiration', [today(), today()->addDays(60)])
            ->orderBy('date_expiration')
            ->limit(3)
            ->get();

        return view('dashboard', compact(
            'stats',
            'accords_par_statut',
            'accords_par_pays',
            'prochaines_reunions',
            'accords_recents',
            'dernieres_activites',
            'accords_expirants'
        ));
    }
}
