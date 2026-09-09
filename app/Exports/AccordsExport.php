<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class AccordsExport
{
    public function __construct(private Collection $accords) {}

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
        return $this->accords->map(fn ($a) => [
            $a->titre,
            $a->reference,
            $a->institution_partenaire,
            $a->etape_label,
            $a->date_arrivee?->format('d/m/Y') ?? '—',
            $a->envoye_le?->format('d/m/Y') ?? '—',
            $a->date_signature?->format('d/m/Y') ?? '—',
            $a->date_expiration?->format('d/m/Y') ?? '—',
            $a->createur->nom_complet,
            $a->created_at->format('d/m/Y'),
        ])->all();
    }

    private function headings(): array
    {
        return [
            'Intitulé de l\'accord',
            'Référence MESRS',
            'Institution partenaire',
            'Étape',
            'Date d\'arrivée',
            'Envoyé le',
            'Date signature',
            'Date expiration',
            'Enregistré par',
            'Date enregistrement',
        ];
    }
}
