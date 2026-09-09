<?php

namespace App\Http\Controllers;

use App\Models\Accord;
use App\Models\AccordHistorique;
use App\Models\Reunion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->latest()->limit(15)->get();
        $notificationsNonLues = Auth::user()->unreadNotifications()->count();

        $stats = [
            'reunions_total' => Reunion::count(),
            'reunions_planifiees' => Reunion::where('statut', 'planifiee')->count(),
            'reunions_terminees' => Reunion::where('statut', 'terminee')->count(),
            'accords_total' => Accord::count(),
            'accords_en_attente' => Accord::whereNull('date_signature')->count(),
            'accords_signes' => Accord::whereNotNull('date_signature')->count(),
            'users_total' => User::where('actif', true)->count(),
        ];

        $accords_par_etape = [];
        foreach (Accord::$etapeLabels as $key => $label) {
            $accords_par_etape[$label] = match ($key) {
                'recu' => Accord::doesntHave('appreciation')->count(),
                'apprecie' => Accord::has('appreciation')->whereNull('envoye_le')->count(),
                'envoye' => Accord::whereNotNull('envoye_le')->whereNull('date_signature')->count(),
                'signe' => Accord::whereNotNull('date_signature')->count(),
            };
        }

        $prochaines_reunions = Reunion::where('date', '>=', today())
            ->where('statut', 'planifiee')
            ->orderBy('date')
            ->limit(5)
            ->get();

        $accords_recents = Accord::with('createur')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $dernieres_activites = AccordHistorique::with(['accord', 'modificateur'])
            ->orderByDesc('date_modification')
            ->limit(6)
            ->get();

        $accords_expirants = Accord::whereNotNull('date_expiration')
            ->whereBetween('date_expiration', [today(), today()->addDays(60)])
            ->orderBy('date_expiration')
            ->limit(3)
            ->get();

        return view('dashboard', compact(
            'stats',
            'accords_par_etape',
            'prochaines_reunions',
            'accords_recents',
            'dernieres_activites',
            'accords_expirants',
            'notifications',
            'notificationsNonLues'
        ));
    }
}
