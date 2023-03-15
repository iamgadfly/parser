<?php

namespace App\Console\Commands;

use App\Actions\PriceDeliveryAction;
use App\Http\Controllers\ParserController;
use App\Models\PostMeta;
use App\Repositories\ProductRepository;
use App\Services\ParserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ParseByLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse_one {--product_id=}';

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
    public function handle(ParserService $parserService, ProductRepository $productRepository, PriceDeliveryAction $action)
    {
        $product_id = $this->option('product_id');
        $product = $productRepository->getByBackMarketId($product_id);
        $product_parsed_data_state =  $parserService->getDataState($parserService->getApiBackmarket($product_id));
        $parsed_data = $parserService->getDataFromParsedData($product, $parserService->getApiBackmarket($product_id, false), $product_parsed_data_state);

        $product  = DB::select(DB::raw("SELECT * FROM wp_posts p JOIN wp_postmeta pm1 ON ( pm1.post_id = p.ID) WHERE p.post_type in('product', 'product_variation') AND p.post_status = 'publish' and pm1.meta_value = '$product_id' LIMIT 1"));
        $product = $product[0] ?? null;
        $product_state = substr($product->post_name, strrpos($product->post_name, '-') + 1);
        $state_data = match($product_state){
            'horoshee' =>  $parsed_data['states'][0],
            'otlichnoe' => $parsed_data['states'][1],
            'kak-novyj' => $parsed_data['states'][2],
        };
        $stock = $parserService->getStock($state_data['in_stock']);
        $post_parent_id = DB::table('wp_posts')->select('post_parent')->where('ID', $product->post_id)->first();
        $price = $action($post_parent_id->post_parent, $state_data['price']);

        $productRepository->updateParserData($price, $product->post_id, '_sale_price');
        $productRepository->updateParserData($stock, $product->post_id, '_stock_status');

        dd('Продукт успешно обнолвен');
    }
}
