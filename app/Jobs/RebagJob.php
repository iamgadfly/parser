<?php

namespace App\Jobs;

use App\Repositories\ProductRepository;
use App\Services\RebagService;
use App\Services\TranslateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RebagJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $rebagService;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->rebagService = new RebagService(new TranslateService(), new ProductRepository());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		logger('test_rebag', ['start']);
        $this->rebagService->getApiRebag();
    }
}
