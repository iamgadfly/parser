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
			//$product->post_parent
			$atrributes = DB::table('wp_postmeta')->where('post_id', 9318)->where('meta_key', '_product_attributes')->first();
			$update_data = unserialize($atrributes->meta_value);
			$data = array_merge($update_data, [ 
			    [
				'name' => 'brand',    
				'id' => '37',
        			'position' => 0,
	        		'variation' => false,
		        	'options' => array($data_state['trackingDetails']['brand']),
  	   		    ]
			]
			);
			$data['brand'] = $data[0];
			unset($data[0]);
//			dd($data);
			dd(self::curl($product->post_parent, $data));
			//'brand' => $data_state['trackingDetails']['brand'],
			
			//DB::table('wp_posts')->where('ID', $product->post_parent)->update([
			//	'brand' => $data_state['trackingDetails']['brand'],
		//	]);
		}
        }
	dd('успешно');
    }
    public static function curl($id, $data)
    {
    	$curl = curl_init();
	curl_setopt_array($curl, array(
	CURLOPT_URL => "https://recommerce-dev.ru/wp-json/wc/v3/products/$id?consumer_key=ck_6d4c35ca173023bbbc1a48bd17e7b54d96e995b3&consumer_secret=cs_22bfcdefeb71ac4d0ad36668de5ef65c958bdb05",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'PUT',
	CURLOPT_POSTFIELDS => json_encode($data),
	CURLOPT_HTTPHEADER => array(
    	'Content-Type: application/json'
),
));
	    $response = curl_exec($curl);

	    curl_close($curl);
	    return json_decode($response);
    }
}
