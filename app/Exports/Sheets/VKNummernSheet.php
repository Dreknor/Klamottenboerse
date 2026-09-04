<?php
namespace App\Exports\Sheets;

use App\Model\verkaufteartikel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class VKNummernSheet implements FromQuery, WithTitle, WithHeadings
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
            ->groupBy('vknummer')
            ->selectRaw('vknummer, sum(betrag) as sum')
            ->orderBy('sum', 'DESC');
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Verkäufer Abrechnung';
    }

    public function headings(): array
    {
       return [
              'VK Nummer',
              'Betrag'
       ];
    }
}
