<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RowCounter;
use App\Imports\RowsImport;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use App\Events\AllJobsCompleted;

class ProcessExcelFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileName;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
        $this->onQueue('excel-processing');
    }

    public function handle()
    {
        try {
            
            \Log::info('Начало обработки файла: ' . $this->fileName);
            
            $filePath = Storage::disk('public')->path('excel/' . $this->fileName);

            $rowCounter = new RowCounter();
            Excel::import($rowCounter, $filePath);
            $totalRows = $rowCounter->getRowCount();

            \Log::info('Общее количество строк: ' . $totalRows);

            $timestamp = time();
            $fileKey = 'file_processing:' . $this->fileName . ':' . $timestamp;
            Redis::hmset($fileKey, ['processed' => 0, 'total' => $totalRows]);
            Redis::expire($fileKey, 86400); // 24 часа

            $import = new RowsImport($this->fileName, $timestamp, $totalRows);
            Excel::import($import, $filePath);

            \Log::info('Завершение обработки файла: ' . $this->fileName);
        

        } catch (\Exception $e) {
            \Log::error('Ошибка при обработке файла: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}
