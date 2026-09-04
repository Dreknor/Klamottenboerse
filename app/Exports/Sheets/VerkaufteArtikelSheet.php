<?php
namespace App\Exports\Sheets;

use App\Model\verkaeufe;
use App\Model\verkaufteartikel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class VerkaufteArtikelSheet implements FromQuery, WithTitle, WithHeadings, WithMapping
{



    public function __construct()
    {

    }

    /**
     * @return Builder
     */
    public function query()
    {
        return verkaufteartikel::query()
            ->orderBy('vknummer', 'ASC');



    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'verkaufte Artikel';
    }

    public function headings(): array
    {
       return [
            'VK Nummer',
            'Artikelnr.',
           'Preis',
       ];
    }

    public function map($row): array
    {
        return [
            $row->vknummer,
            $row->artikelnummer,
            $row->betrag,
        ];
    }
}
