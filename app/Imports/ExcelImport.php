<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Excel;
use Carbon\Carbon;

class ExcelImport implements ToModel
{

    public function model(array $row)
    {
        return new Excel([
            'id' => (int) $row[0],
            'name' => $row[1],
            'date' => $row[2],
        ]);
    }
} 