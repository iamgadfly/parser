<?php

namespace App\Services;

use App\Actions\PriceDeliveryAction;
use App\Models\PostMeta;
use App\Repositories\CourseRepository;
use App\Repositories\ProductRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ParserService
{
    public function parseByLinks(ProductRepository $productRepository): void
    {
        $products = $productRepository->getAllProducts();
        $dollar_course = $productRepository->getCourseByName('Доллар');
        $snopfan_course = $productRepository->getCourseByName('Shopfans');
        foreach ($products as $product){
            if(empty($product->backmarket_id) ||is_null($product->backmarket_id) || $product->backmarket_id == ''){
            logger('bug_empty_url', $product);
            continue;
            }
	    
	    $product_parsed_data_state = $this->getDataState($this->getApiBackmarket($product->backmarket_id));
            $data_state = $this->getApiBackmarket($product->backmarket_id, false);
            $parsed_data = $this->getDataFromParsedData($product, $data_state, $product_parsed_data_state);
            $state_data = match($product->state){
                'horoshee' =>  $parsed_data['states'][0] ?? null,
                'otlichnoe' => $parsed_data['states'][1] ?? null,
                'kak-novyj' => $parsed_data['states'][2] ?? null,
            };
            if(!is_null($state_data['price'])){
            $weight = PriceDeliveryAction::getWeightByCategory($product->product_category);
            $delivery = PriceDeliveryAction::getDeliveryByWeightAndPrice($weight, $state_data['price']) ?? null;
                if(is_null($delivery)){
                    logger('bug', ['weight'=> $weight, 'price' => $state_data['price'], 'product' => $product]);
                    continue;
                }
                $customs_comisson = PriceDeliveryAction::getCustomsСommissionsByWeightAndPrice($weight, $state_data['price']);
		if(is_null($customs_comisson)){
                    logger('bug', ['weight'=> $weight, 'price' => $state_data['price']]);
                    continue;
                }
                $price = PriceDeliveryAction::priceCalculate($weight, $state_data['price'], $dollar_course, $delivery, $snopfan_course, $customs_comisson, 1.1, 1.05);
                $stock = $this->getStock($state_data['in_stock']);
                $count = $this->getCount($state_data);
            } else {
                $stock = 'outofstock';
                $count = 0;
                $price = $product->price != '' ? $product->price : 0;
            }
	  
	    if(!empty($product->post_id)){
            $post_ids[] = $product->post_id;
            //$links[] = $data_state['links']['US']['href'];
            $query_price[] = $price;
            $post_id = $product->post_id;
            $query_status[] = "WHEN post_id = $post_id THEN '$stock'";
            $query_value[] = "WHEN post_id = $post_id THEN '$count'";
	    }
	    if(is_null($state_data)){
		 $state_data = [];
	    }
	    $this->writeLog($state_data, $product->backmarket_id);

	    if (!isset($check_product[$product->post_parent][$product->post_id])){
                $check_product [$product->post_parent][$product->post_id] = $stock;
            } else {
	 	$check_product = [];
	   }
        }


        $parent = $this->updateProductParent($check_product);
	//  $links_query = implode(' ', $links);
      
	$query_sale_price = implode(', ', $query_price);
        $query_stat = implode(' ', $query_status);
        $query_stat_stock = implode(' ', $query_value);
        $product_ids = implode(', ', $post_ids);
        $parent_ids = implode(', ', array_keys($parent));
        $parent_status = implode(' ', array_values($parent));

        $productRepository->updatePrice($product_ids, $query_sale_price);
        $productRepository->updateStockStatus($product_ids, $query_stat, '_stock_status');
        $productRepository->updateStockStatus($product_ids, $query_stat_stock, '_stock');
//        $productRepository->updateStockStatus($product_ids, $links_query, 'backmarket_url');
        $productRepository->updateStockStatus($parent_ids, $parent_status, '_stock_status');

        dd('Продукт успешно обовлен');
    }

    public function parseByLink($product_id, ProductRepository $productRepository, PriceDeliveryAction $action): bool
    {
        $product = $productRepository->getByBackMarketId($product_id);
        $product_parsed_data_state =  $this->getDataState($this->getApiBackmarket($product_id));
        $parsed_data = $this->getDataFromParsedData($product, $this->getApiBackmarket($product_id, false), $product_parsed_data_state);

        $product  = $productRepository->getOneProduct($product_id);
        $product = $product[0] ?? null;
        $product_state = substr($product->post_name, strrpos($product->post_name, '-') + 1);
        $state_data = match($product_state){
            'horoshee' =>  $parsed_data['states'][0],
            'otlichnoe' => $parsed_data['states'][1],
            'kak-novyj' => $parsed_data['states'][2],
        };
        $stock = $this->getStock($state_data['in_stock']);
        $count = $this->getCount($state_data);

        $post_parent_id = DB::table('wp_posts')->select('post_parent')->where('ID', $product->post_id)->first();
        $price = $action($post_parent_id->post_parent, $state_data['price']);

        $productRepository->updateParserData($price, $product->post_id, '_sale_price');
        $productRepository->updateParserData($stock, $product->post_id, '_stock_status');
        $productRepository->updateParserData($count, $product->post_id, '_stock');

        dd('Продукт успешно обнолвен');
    }

    public function getDataState(array $state_data):array | null
    {
        foreach($state_data as $value) {
            if(is_array($value)){
                if(isset($value['backboxGrade']) || isset($value['isDisabled']) || isset($value['price'])) {
                    $data_state []  = [
                        'price' =>  $value['price']['amount'] ?? null,
                        'state' => $value['backboxGrade']['name'],
                        'value' => $value['backboxGrade']['value'],
                        'in_stock' => $value['isDisabled'],
                    ];
                }
            }
        }
        return $data_state ?? null;
    }

    public function updateProductParent(array $states):array
    {
        foreach ($states as $key => $val){
            if(in_array('instock', $states[$key])){
                $data[$key]= "WHEN post_id = $key THEN 'instock'";
            } else {
                $data[$key]= "WHEN post_id = $key THEN 'outofstock'";
            }
        }

        return $data;
    }

    public function getState($sost, $parsed_data):array
    {
        return match($sost){
            'horoshee' =>  $parsed_data['states'][0],
            'otlichnoe' => $parsed_data['states'][1],
            'kak-novyj' => $parsed_data['states'][2],
        };
    }

    public function getStock($stock)
    {
        return match ($stock){
            true => 'outofstock',
            false => 'instock',
        };
    }

    public function getCount($state_data):int
    {
        if($state_data['in_stock'] === false || $state_data['in_stock'] === true  && isset($state_data['stock'])){
            $count = $state_data['value'];
        } else if ($state_data['in_stock'] === false && !isset($state_data['stock'])){
            $count = 10;
        } else {
            $count = 0;
        }
        return $count;
    }

    public function getApiBackMarket(string $product_id, bool $is_state = true):array | false
    {
        $url = match ($is_state){
          true => "https://www.backmarket.com/bm/product/$product_id/v3/best_offers",
            false => "https://www.backmarket.com/bm/product/v2/$product_id",
        };

        $response = Http::get($url) ?? false;
        if($response === false){
            return false;
        }

        return json_decode($response->body(), true);
    }


    public function writeLog(array $data, string $product_id):void
    {
        $fp = fopen(Storage::path('parser/parse.log'), 'a');
        if(!empty($data)){
            $text = " товар с ID $product_id спаршен";
        } else {
            $text = " товар с ID $product_id не спаршен";
        }

        fwrite($fp, Carbon::now()->format('d-m-Y H:m:s') . $text . PHP_EOL);
        fclose($fp);
    }

    public function getDataFromParsedData($product, $data_product, $data_state):array
    {
        return [
            'states' => $data_state ?? null,
        ];
    }
}
