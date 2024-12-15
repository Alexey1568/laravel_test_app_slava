<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class RowCounter implements ToCollection, WithChunkReading, WithHeadingRow
{
    private $rowCount = 0;

    public function collection(Collection $rows)
    {
        $this->rowCount += $rows->count();
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }
} 