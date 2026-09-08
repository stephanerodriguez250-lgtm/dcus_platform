<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ReunionsExport
{
    public function __construct(private Collection $reunions) {}

    public function toCsv(): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($this->headings(), null, 'A1');
        $sheet->fromArray($this->rows(), null, 'A2');

        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(';');
        $writer->setEnclosure('"');
        $writer->setUseBOM(true);

        $stream = fopen('php://temp', 'r+');
        $writer->save($stream);
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $content;
    }

    private function rows(): array
    {
        return $this->reunions->map(fn ($r) => [
            $r->titre,
            $r->date->format('d/m/Y'),
            $r->heure ?? '—',
            $r->lieu,
            $r->convocateur ?? '—',
            $r->statut_label,
            $r->accords()->count(),
            $r->compte_rendu ? 'Oui' : 'Non',
            $r->createur->nom_complet,
            $r->created_at->format('d/m/Y'),
        ])->all();
    }

    private function headings(): array
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
}
