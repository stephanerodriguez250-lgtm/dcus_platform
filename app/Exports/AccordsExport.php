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

class AccordsExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(private Collection $accords) {}

    public function collection(): Collection
    {
        return $this->accords->map(fn($a) => [
            'titre'                  => $a->titre,
            'pays_partenaire'        => $a->pays_partenaire,
            'institution_partenaire' => $a->institution_partenaire,
            'universite'             => $a->universite_beneficiaire ?? '—',
            'statut'                 => $a->statut_label,
            'reunion_origine'        => $a->reunion?->titre ?? '—',
            'date_identification'    => $a->date_identification?->format('d/m/Y') ?? '—',
            'date_signature'         => $a->date_signature?->format('d/m/Y') ?? '—',
            'date_expiration'        => $a->date_expiration?->format('d/m/Y') ?? '—',
            'cree_par'               => $a->createur->nom_complet,
            'date_creation'          => $a->created_at->format('d/m/Y'),
        ]);
    }

    public function headings(): array
    {
        return [
            'Intitulé de l\'accord',
            'Pays partenaire',
            'Institution partenaire',
            'Université bénéficiaire',
            'Statut',
            'Réunion d\'origine',
            'Date identification',
            'Date signature',
            'Date expiration',
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
        return 'Accords DCUS';
    }
}
