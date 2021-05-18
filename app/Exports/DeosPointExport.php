<?php

namespace App\Exports;

use App\Models\DeosPoint;
use Maatwebsite\Excel\Concerns\FromCollection;

class DeosPointExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DeosPoint::all();
    }
}
