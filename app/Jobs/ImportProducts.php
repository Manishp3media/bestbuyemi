<?php

namespace App\Jobs;

use App\Services\ProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $filePath;
    protected $addedBy;
    protected $jobId;
    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $addedBy, $jobId)
    {
        $this->filePath = $filePath;
        $this->addedBy = $addedBy;
        $this->jobId = $jobId;

    }

    /**
     * Execute the job.
     */
    public function handle(ProductService $productService)
    {
        (new FastExcel)->import(storage_path($this->filePath), function ($row) use ($productService) {
            $trimmedRow = array_map('trim', $row);
            $productService->getNewImportBulkProductData($trimmedRow, $this->addedBy,$this->jobId);
        });

        // Optionally, you can delete the chunk file after processing
        Storage::delete($this->filePath);
         // Update job progress
         DB::table('job_progress')
         ->where('job_id', $this->jobId)
         ->increment('completed_jobs');
    }
}
