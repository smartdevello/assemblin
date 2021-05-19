<?php

namespace App\Exports;

use App\Models\DEOS_point;
use Maatwebsite\Excel\Concerns\FromCollection;

class DeosPointExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DEOS_point::all();
    }
}
