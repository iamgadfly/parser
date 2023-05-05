<?php

namespace App\Console\Commands;

use App\Repositories\ProductRepository;
use Illuminate\Console\Command;
use App\Services\ParserService;
use Illuminate\Support\Facades\DB;

class InsertBrandCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert_brand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
     
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ProductRepository $productRepository, ParserService $parserService)
    {
	$products = $productRepository->getAllProducts();
	$products = collect($products)->unique('post_parent');
        foreach ($products as $product) {
		if(is_null($product->backmarket_id)){
		continue;
		}
		$data_state = $parserService->getApiBackmarket($product->backmarket_id, false);
		if(isset($data_state['trackingDetails']['brand'])){
			//$brands[] = $data_state['trackingDetails']['brand'];
			//$parent_ids[] = $product->post_parent;
			DB::table('wp_posts')->where('ID', $product->post_parent)->update([
				'brand' => $data_state['trackingDetails']['brand'],
			]);
		}
        }
	dd('успешно');
    }
}
