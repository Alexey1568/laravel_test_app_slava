<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use App\Jobs\ProcessExcelRow;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class RowsImport implements ToCollection, WithChunkReading, WithHeadingRow, WithBatchInserts
{
    use RemembersRowNumber;

    private $lineNumber = 2;
    private $fileName;
    private $timestamp;
    private $totalRows;

    public function __construct($fileName, $timestamp, $totalRows)
    {
        $this->fileName = $fileName;
        $this->timestamp = $timestamp;
        $this->totalRows = $totalRows;
    }

    public function collection(Collection $rows)
    {
        \Log::info('Обработка чанка. Количество строк: ' . $rows->count());
        
        foreach ($rows as $row) {
            $rowData = $row->toArray();
            $rowData['line_number'] = $this->lineNumber++;
            $rowData['file_name'] = $this->fileName;
            $rowData['timestamp'] = $this->timestamp;
            
            \Log::info('Создание джобы для строки: ' . $rowData['line_number']);
            
            ProcessExcelRow::dispatch($rowData, $this->totalRows)->onQueue('excel-processing');
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
} 