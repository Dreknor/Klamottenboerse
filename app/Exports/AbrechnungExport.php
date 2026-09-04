<?php

namespace App\Exports;

use App\Exports\Sheets\VerkaufteArtikelSheet;
use App\Exports\Sheets\VerkäufeSheet;
use App\Exports\Sheets\VKNummernSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AbrechnungExport implements FromCollection, ShouldAutoSize, WithMultipleSheets
{
    use Exportable;


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
    }

    public function sheets(): array
    {
        $sheets = [
            new VKNummernSheet(),
            new VerkäufeSheet(),
            new VerkaufteArtikelSheet()

        ];

        return $sheets;

    }


}
