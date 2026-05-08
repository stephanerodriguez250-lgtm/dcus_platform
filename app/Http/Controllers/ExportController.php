<?php

namespace App\Http\Controllers;

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
        return $pdf->download('reunions-dcus-' . now()->format('Y-m-d') . '.pdf');
    }

    // ─── PDF Accords ─────────────────────────────────────────────
    public function accordsPdf(Request $request)
    {
        $accords = $this->filtrerAccords($request);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('exports.accords-pdf', compact('accords'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('accords-dcus-' . now()->format('Y-m-d') . '.pdf');
    }

    // ─── Excel Réunions ──────────────────────────────────────────
    public function reunionsExcel(Request $request)
    {
        $reunions = $this->filtrerReunions($request);
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ReunionsExport($reunions),
            'reunions-dcus-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    // ─── Excel Accords ───────────────────────────────────────────
    public function accordsExcel(Request $request)
    {
        $accords = $this->filtrerAccords($request);
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AccordsExport($accords),
            'accords-dcus-' . now()->format('Y-m-d') . '.xlsx'
        );
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
