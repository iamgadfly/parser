<?php

namespace App\Services;

use App\Actions\PriceDeliveryAction;
use App\Models\PostMeta;
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
        foreach ($products as $product){
            $product_parsed_data_state = $this->getDataState($this->getApiBackmarket($product->backmarket_id));
            $parsed_data = $this->getDataFromParsedData($product, $this->getApiBackmarket($product->backmarket_id, false), $product_parsed_data_state);
            $state_data = match($product->state){
                'horoshee' =>  $parsed_data['states'][0] ?? null,
                'otlichnoe' => $parsed_data['states'][1] ?? null,
                'kak-novyj' => $parsed_data['states'][2] ?? null,
            };
            if(empty($state_data['price'])){
                $this->writeLog([], $product->backmarket_id);
                continue;
            }
            $stock = $this->getStock($state_data['in_stock']);
            $post_ids[] = $product->post_id;
            $price = $this->calculatePrice($product->product_category, $state_data['price']);
            $query_price[] = $price;
            $query_status[] = "WHEN post_id = $product->post_id THEN '$stock'";
            $this->writeLog($state_data, $product->backmarket_id);
        }
        $query_sale_price = implode(', ', $query_price);
        $query_stat = implode(' ', $query_status);
        $product_ids = implode(', ', $post_ids);
        DB::select(DB::raw("UPDATE `wp_postmeta` SET meta_value = ELT(FIELD(post_id, $product_ids), $query_sale_price) WHERE post_id IN ($product_ids) and meta_key='_sale_price';"));
        DB::update("UPDATE wp_postmeta SET meta_value = CASE $query_stat END WHERE post_id IN ($product_ids) and meta_key='_stock_status'");

        dd('Продукт успешно обовлен');
    }

    public function parseByLink($product_id, ProductRepository $productRepository, PriceDeliveryAction $action): bool
    {
        $product = $productRepository->getByBackMarketId($product_id);
        $product_parsed_data_state =  $this->getDataState($this->getApiBackmarket($product_id));
        $parsed_data = $this->getDataFromParsedData($product, $this->getApiBackmarket($product_id, false), $product_parsed_data_state);

        $product  = DB::select(DB::raw("SELECT * FROM wp_posts p JOIN wp_postmeta pm1 ON ( pm1.post_id = p.ID) WHERE p.post_type in('product', 'product_variation') AND p.post_status = 'publish' and pm1.meta_value = '$product_id' LIMIT 1"));
        $product = $product[0] ?? null;
        $product_state = substr($product->post_name, strrpos($product->post_name, '-') + 1);
        $state_data = match($product_state){
            'horoshee' =>  $parsed_data['states'][0],
            'otlichnoe' => $parsed_data['states'][1],
            'kak-novyj' => $parsed_data['states'][2],
        };
        $stock = $this->getStock($state_data['in_stock']);
        $post_parent_id = DB::table('wp_posts')->select('post_parent')->where('ID', $product->post_id)->first();
        $price = $action($post_parent_id->post_parent, $state_data['price']);

        $productRepository->updateParserData($price, $product->post_id, '_sale_price');
        $productRepository->updateParserData($stock, $product->post_id, '_stock_status');

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
            true => 'instock',
            false => 'outofstock',
        };
    }


    public function getApiBackMarket(string $product_id, bool $is_state = true):array | false
    {
        $url = match ($is_state){
          true => "https://www.backmarket.com/bm/product/$product_id/v3/best_offers",
            false => "https://www.backmarket.com/bm/product/v2/$product_id",
        };

        return json_decode(Http::get($url)->body(), true) ?? false;
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
//            'id' => $product->post_id,
//            'price_new' => $data_product['priceWhenNew']['amount'] ?? null,
//            'model' => $data_product['model'] ?? null,
//            'backmarket_id' => $product->backmarket_id,
//            'link' => $data_product['links']['US']['href'] ?? null,
//            'model_about' => $response['subTitleElements'] ?? null,
            'states' => $data_state ?? null,
        ];
    }
    public function calculatePrice($product_category, $raw_price):int
    {
        $weight = $this->getWeightByCategory($product_category);
        $delivery = match(true){
            $weight == 1 && $raw_price > 450 => self::getDelivery($weight, 'Shopfans'),
            $weight == 1.5 && $raw_price > 450 =>  self::getDelivery($weight, 'Shopfans'),
            $weight == 3.5 && $raw_price > 450 => self::getDelivery($weight, 'Shopfans'),
            $weight == 15 && $raw_price > 450 => self::getDelivery($weight, 'Shopfans'),
            default => self::getDelivery($weight, 'Onex'),
        };

        $snopfan_course = DB::table('courses')->where('name', 'Shopfans')->first()->price;
        $dollar_course = DB::table('courses')->where('name', 'Доллар')->first()->price;

        return intval(PriceDeliveryAction::priceCalculate($weight, $raw_price, $dollar_course, $delivery->price, $snopfan_course));
    }

    public static function getPriceSnopfansDelivery($dollar_course, $raw_price, $delivery, $snopfan_course, $customs_comisson, $agent_comission, $payment_comisson)
    {
        return intval($dollar_course * ($raw_price * $agent_comission) + ($delivery + $customs_comisson) * $snopfan_course * $payment_comisson);
    }

    public static function getPriceOnexDeliveryWithCustoms($dollar_course, $raw_price, $delivery, $customs_comisson, $agent_comission, $payment_comisson)
    {
        return intval($dollar_course * ($raw_price * $agent_comission) + $delivery * $dollar_course * $payment_comisson);
    }

    public static function getPriceOnexDeliveryWithoutCustoms($dollar_course, $raw_price, $delivery, $agent_comission, $payment_comisson, $customs_comisson)
    {
        return intval($dollar_course * $raw_price * $agent_comission + (($delivery + ($raw_price - 380) * $customs_comisson) * $dollar_course) * $payment_comisson);
    }

    public function getDelivery($weight, $name)
    {
        $weight_price =  DB::table('deliveries')->where([
            ['weight', '=', $weight],
            ['name', '=', $name],
        ])->first();
        return $weight_price;
    }

    public function getWeightByCategory($product_category)
    {
        return match($product_category){
            'smartfony' => 1,
            'vse-mobilnye-ustrojstva' => 1,
            'Все мобильные устройства' => 1,
            'Смартфоны' => 1,
            'Apple' => 1,
            'iPhone' => 1,

            'smart-chasy' => 1,
            'apple' => 1,
            'iphone' => 1,
            'planshety' => 1.5,

            'Планшеты' => 1.5,
            'iPad' => 1.5,

            'MacBook' => 3.5,
            'noutbuki' => 3.5,
            'Ноутбуки' => 3.5,

            'monobloki' => 15,
            'Моноблоки' => 15,
        };
    }
}
