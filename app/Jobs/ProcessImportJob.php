<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ProcessImport\ProcessImportHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $importId) {}

    /**
     * Execute the job.
     */
    public function handle(ProcessImportHandler $handler): void
    {
        $handler->handle($this->importId);
    }
}
