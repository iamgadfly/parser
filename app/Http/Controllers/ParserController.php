<?php

namespace App\Http\Controllers;

use App\Actions\PriceDeliveryAction;
use App\Imports\ProductsImport;
use App\Services\PriceService;
use Carbon\Carbon;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ParserController extends Controller
{
    public static function index($action = new PriceDeliveryAction){
        $file = Storage::disk('local')->get('all.csv');
        foreach(explode(',', $file) as $value){
              if(stristr($value, 'https://www.backmarket.com/en-us/p/')){
                $links[] = $value;
              }
        }

        foreach($links as $link){
            $link = substr($link, strrpos($link, '/') + 1);
            if(stristr($link, '#') != false){
                $product_ids [] = stristr($link, '#', true);
            } else {
                $product_ids [] = $link;
            }
        }
        // $product_ids = DB::table('wp_postmeta')->where('meta_key', 'backmarket_id')->pluck('meta_value');
        // SELECT post_id FROM wp_postmeta WHERE meta_key = 'backmarket_id' and meta_value = 'b5303639-e51f-47e0-b766-a42fc9e794e8';
        $path = Storage::path('parser/parse.log');
        foreach($product_ids as $product_id){
            $response = Http::get("https://www.backmarket.com/bm/product/v2/$product_id");
            $response2 = Http::get("https://www.backmarket.com/bm/product/$product_id/v3/best_offers");
            $response_data = json_decode($response->body(), true);
            $response_data_about = json_decode($response2->body(), true);
            if(empty($response_data_about) || empty($response_data)){
                Log::error('ERROR', [
                    'status_code' =>  $response->status(),
                    'response' => $response_data,
                ]);
                return response()->json(['Error' => 'Что-то пошло не так'], 400);
            }

            foreach($response_data_about as $value) {
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
            $data [] = [
                'price_new' => $response_data['priceWhenNew']['amount'] ?? null,
                'model' => $response_data['model'] ?? null,
                'product_id' => $product_id,
                'link' => $response_data['links']['US']['href'] ?? null,
                'model_about' => $response['subTitleElements'] ?? null,
                'states' => $data_state ?? null,
	        ];
            unset($data_state);
            $fp = fopen($path, 'a');
            if(!empty($data)){
                fwrite($fp, Carbon::now()->format('d-m-Y H:m:s') . " товар с ID $product_id спаршен" . PHP_EOL);
            } else {
                fwrite($fp, Carbon::now()->format('d-m-Y H:m:s') . " товар с ID $product_id не спаршен" . PHP_EOL);
            }
            fclose($fp);
	}
            foreach($data as $value){
                $product  = DB::select(DB::raw("SELECT * FROM wp_posts p JOIN wp_postmeta pm1 ON ( pm1.post_id = p.ID) WHERE p.post_type in('product', 'product_variation') AND p.post_status = 'publish' and pm1.meta_value = '$product_id' LIMIT 1"));
                $product = $product[0];
                if(!empty($product) && !is_null($value['states']) && !is_null($value['price_new'])){
                $product_state = substr($product->post_name, strrpos($product->post_name, '-') + 1);
                $state_data = match($product_state){
                    'horoshee' =>  $value['states'][0],
                    'otlichnoe' => $value['states'][1],
                    'kak-novyj' => $value['states'][2],
                };
                $price = $action($product, $state_data['price']);

                DB::table('wp_postmeta')->where([
                    'post_id' => $product->post_id,
                    'meta_key' => '_sale_price',
                ])->update(['meta_value' =>  $price]);

                    if($state_data['in_stock']){
                        DB::table('wp_postmeta')->where([
                            'post_id' => $product->post_id,
                            'meta_key' => '_stock_status',
                        ])->update([
                            'meta_value' => 'instock',
                        ]);
                    } else {
                        DB::table('wp_postmeta')->where([
                            'post_id' => $product->post_id,
                            'meta_key' => '_stock_status',
                        ])->update(['meta_value' => 'outofstock',]);
                    }
                } else {
                    Log::error('ID продукта нет в базе:', [$value['product_id']]);
                }
            }
        //  dd($data);
    }

    public static function parseByOneId($product_id, $is_command = true,  $action = new PriceDeliveryAction)
    {
        $response = Http::get("https://www.backmarket.com/bm/product/v2/$product_id");
        $response2 = Http::get("https://www.backmarket.com/bm/product/$product_id/v3/best_offers");
        $response_data = json_decode($response->body(), true);
        $response_data_about = json_decode($response2->body(), true);
        if(empty($response_data_about) || empty($response_data)){
            Log::error('ERROR', [
                'status_code' =>  $response->status(),
                'response' => $response_data,
            ]);
            return response()->json(['Error' => 'Что-то пошло не так'], 400);
        }

        foreach($response_data_about as $value) {
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

        $data [] = [
            'price_new' => $response_data['priceWhenNew']['amount'] ?? null,
            'model' => $response_data['model'] ?? null,
            'product_id' => $product_id,
            'link' => $response_data['links']['US']['href'] ?? null,
            'model_about' => $response['subTitleElements'] ?? null,
            'states' => $data_state ?? null,
            ];

            $path = Storage::path('parser/parse.log');
            $fp = fopen($path, 'a');
            if(!empty($data)){
                fwrite($fp, Carbon::now()->format('d-m-Y H:m:s') . " товар с ID $product_id спаршен" . PHP_EOL);
            } else {
                fwrite($fp, Carbon::now()->format('d-m-Y H:m:s') . " товар с ID $product_id не спаршен" . PHP_EOL);
            }
            fclose($fp);

            if(empty($data)){
                dd('Что-то пошло не так');
            }
            $product  = DB::select(DB::raw("SELECT * FROM wp_posts p JOIN wp_postmeta pm1 ON ( pm1.post_id = p.ID) WHERE p.post_type in('product', 'product_variation') AND p.post_status = 'publish' and pm1.meta_value = '$product_id' LIMIT 1"));
            $product = $product[0] ?? null;
            if(!is_null($product) && !is_null($data[0]['states']) && !is_null($data[0]['price_new'])){
            $product_state = substr($product->post_name, strrpos($product->post_name, '-') + 1);
            $state_data = match($product_state){
                'horoshee' =>  $data[0]['states'][0],
                'otlichnoe' => $data[0]['states'][1],
                'kak-novyj' => $data[0]['states'][2],
            };
            $price = $action($product, $state_data['price']);
            // dd($price);
            // DB::table('wp_postmeta')->where([
            //     'post_id' => $product->post_id,
            //     'meta_key' => '_regular_price',
            // ])->update(['meta_value' => $price_new]);

            DB::table('wp_postmeta')->where([
                'post_id' => $product->post_id,
                'meta_key' => '_sale_price',
            ])->update(['meta_value' =>  $price]);

                if($state_data['in_stock']){
                    DB::table('wp_postmeta')->where([
                        'post_id' => $product->post_id,
                        'meta_key' => '_stock_status',
                    ])->update([
                        'meta_value' => 'instock',
                    ]);
                } else {
                    DB::table('wp_postmeta')->where([
                        'post_id' => $product->post_id,
                        'meta_key' => '_stock_status',
                    ])->update(['meta_value' => 'outofstock',]);
                }
            } else {
                $prod_id = $data[0]['product_id'];
                dd("ID продукта нет в базе: $prod_id");
            }

            match($is_command){
                true => dd($data),
            };

            return $data;
            // dd($data);
    }
}
