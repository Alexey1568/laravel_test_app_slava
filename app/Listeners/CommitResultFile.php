<?php

namespace App\Listeners;

use App\Events\AllJobsCompleted;

class CommitResultFile
{
    public function handle(AllJobsCompleted $event)
    {
        $output = [];
        $returnVar = 0;

        exec('git add storage/app/result.txt 2>&1', $output, $returnVar);
        if ($returnVar !== 0) {
            \Log::error('Ошибка при добавлении файла: ' . implode("\n", $output));
        }

        exec('git commit -m "Added result.txt with validation errors" 2>&1', $output, $returnVar);
        if ($returnVar !== 0) {
            \Log::error('Ошибка при коммите: ' . implode("\n", $output));
        }
    }
} 