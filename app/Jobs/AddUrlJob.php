<?php

namespace App\Jobs;

use App\Repositories\ProductRepository;
use App\Services\ParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddUrlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ParserService $parserService, ProductRepository $productRepository)
    {
        $products = $productRepository->getAllProducts();
        foreach ($products as $product) {
            if(is_null($product->backmarket_id)){
                continue;
            }
            $data_state = $parserService->getApiBackmarket($product->backmarket_id, false);
            $link = $data_state['links']['US']['href'] ?? null;
           if(is_null($link)){
	    continue;
	   }
	     $insert_data[] = "($product->post_id, 'backmarket_url', '$link')";
        }
        $insert = implode(', ', array_unique($insert_data));
        $productRepository->insertBackMarketUrl($insert);
        dd("Успешно");
        }
}
