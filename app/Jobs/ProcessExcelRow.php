<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Excel;
use Illuminate\Support\Facades\Redis;

class ProcessExcelRow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $row;
    protected $totalRows;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, $totalRows)
    {
        $this->row = $row;
        $this->totalRows = $totalRows;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $errors = [];
        $lineNumber = $this->row['line_number']; 

   
        if (!is_numeric($this->row['id']) || $this->row['id'] < 0) {
            $errors[] = 'Неверный формат id';
        }

        if (!preg_match('/^[a-zA-Z\s]+$/', $this->row['name'])) {
            $errors[] = 'Неверный формат имени';
        }

        $date = \DateTime::createFromFormat('d.m.Y', $this->row['date']);
        if (!$date || $date->format('d.m.Y') !== $this->row['date']) {
            $errors[] = 'Неверный формат даты';
        }

  
        if (Excel::where('id', $this->row['id'])->exists()) {
            $errors[] = 'Дубликат id';
        }

        if (!empty($errors)) {
            $errorMessage = $lineNumber . ' - ' . implode(', ', $errors);
            Storage::append('result.txt', $errorMessage);
            return;
        }

        Excel::create($this->row);

        // Обновление прогресса в Redis
        $fileKey = 'file_processing:' . $this->row['file_name'] . ':' . $this->row['timestamp'];
        Redis::hincrby($fileKey, 'processed', 1);

        // Очистка данных о прогрессе после завершения
        if ($this->isLastRow()) {
            Redis::del($fileKey);
        }
    }

    private function isLastRow()
    {
        return $this->row['line_number'] === $this->totalRows;
    }
}