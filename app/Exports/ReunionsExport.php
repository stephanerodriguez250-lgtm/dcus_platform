<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReunionsExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(private Collection $reunions) {}

    public function collection(): Collection
    {
        return $this->reunions->map(fn($r) => [
            'titre'         => $r->titre,
            'date'          => $r->date->format('d/m/Y'),
            'heure'         => $r->heure ?? '—',
            'lieu'          => $r->lieu,
            'convocateur'   => $r->convocateur ?? '—',
            'statut'        => $r->statut_label,
            'accords'       => $r->accords()->count(),
            'compte_rendu'  => $r->compte_rendu ? 'Oui' : 'Non',
            'cree_par'      => $r->createur->nom_complet,
            'date_creation' => $r->created_at->format('d/m/Y'),
        ]);
    }

    public function headings(): array
    {
        return [
            'Intitulé de la réunion',
            'Date',
            'Heure',
            'Lieu',
            'Convocateur',
            'Statut',
            'Nb. Accords liés',
            'Compte rendu',
            'Créé par',
            'Date enregistrement',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a3a5c']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function title(): string
    {
        return 'Réunions DCUS';
    }
}
