<?php

namespace App\Imports;

use App\Models\DEOS_point;
use Maatwebsite\Excel\Concerns\ToModel;

class DeosPointImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new DEOS_point([
            'name' => $row[0],
            'sensor' => $row[1]
        ]);
    }
}
