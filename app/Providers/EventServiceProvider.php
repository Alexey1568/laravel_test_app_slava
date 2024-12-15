<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\AllJobsCompleted;
use App\Listeners\CommitResultFile;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AllJobsCompleted::class => [
            CommitResultFile::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
} 