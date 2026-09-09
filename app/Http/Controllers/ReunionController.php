<?php

namespace App\Http\Controllers;

use App\Mail\ConvocationReunion;
use App\Models\Reunion;
use App\Models\User;
use App\Notifications\ReunionCreeeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReunionController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = Reunion::with('createur')->orderByDesc('date');
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('titre', 'like', '%'.$request->search.'%')
                    ->orWhere('lieu', 'like', '%'.$request->search.'%')
                    ->orWhere('convocateur', 'like', '%'.$request->search.'%');
            });
        }
        $reunions = $query->paginate(10)->withQueryString();
        $statuts = Reunion::$statuts;

        return view('reunions.index', compact('reunions', 'statuts'));
    }

    //
    public function create()
    {
        $this->authorize('create', Reunion::class);
        $statuts = Reunion::$statuts;

        return view('reunions.create', compact('statuts'));
    }

    //
    public function store(Request $request)
    {
        $this->authorize('create', Reunion::class);
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'date' => 'required|date',
            'heure' => 'nullable|date_format:H:i',
            'lieu' => 'required|string|max:255',
            'ordre_du_jour' => 'required|string',
            'convocateur' => 'nullable|string|max:255',
            'statut' => 'required|in:'.implode(',', array_keys(Reunion::$statuts)),
        ]);
        $data['created_by'] = Auth::id();
        $reunion = Reunion::create($data);

        foreach (User::actifsSauf(Auth::id()) as $utilisateur) {
            $utilisateur->notify(new ReunionCreeeNotification($reunion, Auth::user()));
        }

        // Envoi des convocations à tous les agents actifs
        $envoyes = $this->envoyerConvocations($reunion);
        $message = $envoyes > 0
            ? "Réunion créée avec succès. {$envoyes} notification(s) envoyée(s)."
            : 'Réunion créée avec succès. Aucun email envoyé (vérifiez la config SMTP).';

        return redirect()->route('reunions.index')
            ->with('success', $message);
    }

    //
    public function show(Reunion $reunion)
    {
        $reunion->load('createur', 'accords', 'decisions');

        return view('reunions.show', compact('reunion'));
    }

    //
    public function edit(Reunion $reunion)
    {
        $this->authorize('update', $reunion);
        $statuts = Reunion::$statuts;

        return view('reunions.edit', compact('reunion', 'statuts'));
    }

    //
    public function update(Request $request, Reunion $reunion)
    {
        $this->authorize('update', $reunion);
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'date' => 'required|date',
            'heure' => 'nullable|date_format:H:i',
            'lieu' => 'required|string|max:255',
            'ordre_du_jour' => 'required|string',
            'compte_rendu' => 'nullable|string',
            'convocateur' => 'nullable|string|max:255',
            'statut' => 'required|in:'.implode(',', array_keys(Reunion::$statuts)),
        ]);
        // Détecter si des infos importantes ont changé
        $dateChangee = $reunion->date->format('Y-m-d') !== $data['date'];
        $heureChangee = ($reunion->heure ?? '') !== ($data['heure'] ?? '');
        $lieuChange = $reunion->lieu !== $data['lieu'];
        $reunion->update($data);
        // Renvoyer automatiquement si date, heure ou lieu ont changé
        $message = 'Réunion mise à jour avec succès.';
        if ($request->boolean('renvoyer_notifications') || $dateChangee || $heureChangee || $lieuChange) {
            $envoyes = $this->envoyerConvocations($reunion);
            if ($envoyes > 0) {
                $message .= " {$envoyes} notification(s) de mise à jour envoyée(s).";
            }
        }

        return redirect()->route('reunions.show', $reunion)
            ->with('success', $message);
    }

    //
    public function destroy(Reunion $reunion)
    {
        $this->authorize('delete', $reunion);
        $reunion->delete();

        return redirect()->route('reunions.index')
            ->with('success', 'Réunion supprimée.');
    }

    //
    public function renvoyerNotifications(Reunion $reunion)
    {
        $this->authorize('update', $reunion);
        $envoyes = $this->envoyerConvocations($reunion);

        return back()->with(
            'success',
            $envoyes > 0
                ? "{$envoyes} notification(s) renvoyée(s) avec succès."
                : 'Aucun email envoyé (vérifiez la configuration SMTP).'
        );
    }

    //
    public function generatePdf(Reunion $reunion)
    {
        $reunion->load(['createur', 'accords', 'decisions']);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('exports.reunion-pdf', compact('reunion'));
        $pdf->setPaper('A4', 'portrait');
        $filename = 'CR-REUNION-'.$reunion->date->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    //
    /**
     * Envoie la convocation à tous les agents actifs.
     * Retourne le nombre d'emails envoyés avec succès.
     */
    private function envoyerConvocations(Reunion $reunion): int
    {
        $agents = User::where('actif', true)->get();
        $envoyes = 0;
        foreach ($agents as $agent) {
            // Ne pas envoyer à un agent sans email
            if (empty($agent->email)) {
                continue;
            }
            try {
                Mail::to($agent->email)
                    ->send(new ConvocationReunion($reunion, $agent));
                $envoyes++;
            } catch (\Exception $e) {
                // Enregistrer l'erreur sans bloquer les autres envois
                Log::error(
                    "Échec envoi convocation réunion #{$reunion->id} ".
                    "à {$agent->email} : ".$e->getMessage()
                );
            }
        }
        // Log du bilan global
        Log::info(
            "Convocations réunion #{$reunion->id} : ".
            "{$envoyes}/{$agents->count()} emails envoyés."
        );

        return $envoyes;
    }
}
