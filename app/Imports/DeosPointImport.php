<?php

namespace App\Imports;

use App\Models\DeosPoint;
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
        return new DeosPoint([
            'name' => $row[0],
            'sensor' => $row[1]
        ]);
    }
}
