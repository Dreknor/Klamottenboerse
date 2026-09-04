<?php
namespace App\Exports\Sheets;

use App\Model\verkaeufe;
use App\Model\verkaufteartikel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class VerkäufeSheet implements FromQuery, WithTitle, WithHeadings, WithMapping
{



    public function __construct()
    {

    }

    /**
     * @return Builder
     */
    public function query()
    {
        return verkaeufe::query()
            ->with('artikel')
            ->orderBy('created_at', 'DESC');



    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Verkäufe';
    }

    public function headings(): array
    {
       return [
        'Kassierer',
        'Artikelanzahl',
        'Betrag',
        'Erstellt am'
       ];
    }

    public function map($row): array
    {
        return [
            $row->user->name,
            count($row->artikel),
            $row->summe,
            $row->created_at->format('d.m.Y H:i:s')
        ];
    }
}
