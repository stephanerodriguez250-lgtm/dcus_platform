<?php

namespace App\Http\Controllers;

use App\Exports\AccordsExport;
use App\Exports\ReunionsExport;
use App\Models\Accord;
use App\Models\Reunion;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    // ─── PDF Réunions ───────────────────────────────────────────
    public function reunionsPdf(Request $request)
    {
        $reunions = $this->filtrerReunions($request);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('exports.reunions-pdf', compact('reunions'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('reunions-dcus-'.now()->format('Y-m-d').'.pdf');
    }

    // ─── PDF Accords ─────────────────────────────────────────────
    public function accordsPdf(Request $request)
    {
        $accords = $this->filtrerAccords($request);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('exports.accords-pdf', compact('accords'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('accords-dcus-'.now()->format('Y-m-d').'.pdf');
    }

    // ─── CSV Réunions ────────────────────────────────────────────
    public function reunionsCsv(Request $request)
    {
        $reunions = $this->filtrerReunions($request);
        $csv = (new ReunionsExport($reunions))->toCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reunions-dcus-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    // ─── CSV Accords ─────────────────────────────────────────────
    public function accordsCsv(Request $request)
    {
        $accords = $this->filtrerAccords($request);
        $csv = (new AccordsExport($accords))->toCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="accords-dcus-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────
    private function filtrerReunions(Request $request)
    {
        $query = Reunion::with('createur')->orderByDesc('date');
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        return $query->get();
    }

    private function filtrerAccords(Request $request)
    {
        $query = Accord::with(['reunion', 'createur'])->orderByDesc('created_at');
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        return $query->get();
    }
}
