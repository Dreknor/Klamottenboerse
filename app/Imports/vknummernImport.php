<?php

namespace App\Imports;

use App\Model\VKnummer;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class vknummernImport implements ToModel, WithHeadingRow
{
    public function __construct()
    {
        $this->klamottenboerse = new KlamottenboersenRepository();
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (isset($row['vknummer']) and isset($row['sum'])) {
            return VKnummer::query()->updateOrCreate([
                'vknummer'  => $row['vknummer'],
                'klamottenboersen_id'   => $this->klamottenboerse->aktuelleKlamottenboerse()->id,
            ], [
                'umsatz'    => $row['sum'],
            ]);
        }
    }
}
