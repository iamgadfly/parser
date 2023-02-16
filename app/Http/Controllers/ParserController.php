<?php

namespace App\Http\Controllers;

use App\Imports\ProductsImport;
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
    public static function index(){
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

            $dollar_price = 73.92;
            foreach($data as $value){
                $prod_id = DB::table('wp_postmeta')->where([
                    'meta_key' => 'backmarket_id',
                    'meta_value' => $value['product_id'],
                ])->select('post_id')->first();

                if(!is_null($prod_id) && !is_null($value['states']) && !is_null($value['price_new'])){
                $product_state = DB::table('wp_postmeta')->where([
                    'meta_key' => 'attribute_pa_sostoyanie',
                    'post_id' => $prod_id->post_id,
                ])->select('meta_value')->first();
                $state_data = match($product_state->meta_value){
                    'horoshee' =>  $value['states'][0],
                    'otlichnoe' => $value['states'][1],
                    'kak-novyj' => $value['states'][2],
                };


                DB::table('wp_postmeta')->where([
                    'post_id' => $prod_id->post_id,
                    'meta_key' => '_regular_price',
                ])->update(['meta_value' => intval($value['price_new'] * $dollar_price),]);

                DB::table('wp_postmeta')->where([
                    'post_id' => $prod_id->post_id,
                    'meta_key' => '_sale_price',
                ])->update(['meta_value' =>  intval($state_data['price'] * $dollar_price)]);

                    if($state_data['in_stock']){
                        DB::table('wp_postmeta')->where([
                            'post_id' => $prod_id->post_id,
                            'meta_key' => '_stock_status',
                        ])->update([
                            'meta_value' => 'instock',
                        ]);
                    } else {
                        DB::table('wp_postmeta')->where([
                            'post_id' => $prod_id->post_id,
                            'meta_key' => '_stock_status',
                        ])->update(['meta_value' => 'outofstock',]);
                    }
                } else {
                    Log::error('ID продукта нет в базе:', [$value['product_id']]);
                }
            }
        //  dd($data);
    }

    public static function parseByOneId($product_id)
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

            $dollar_price = 73.92;
            if(empty($data)){
                dd('Что-то пошло не так');
            }

            $prod_id = DB::table('wp_postmeta')->where([
                'meta_key' => 'backmarket_id',
                'meta_value' => $data[0]['product_id'],
            ])->select('post_id')->first();

            if(!is_null($prod_id) && !is_null($data[0]['states']) && !is_null($data[0]['price_new'])){
            $product_state = DB::table('wp_postmeta')->where([
                'meta_key' => 'attribute_pa_sostoyanie',
                'post_id' => $prod_id->post_id,
            ])->select('meta_value')->first();
            $state_data = match($product_state->meta_value){
                'horoshee' =>  $data[0]['states'][0],
                'otlichnoe' => $data[0]['states'][1],
                'kak-novyj' => $data[0]['states'][2],
            };


            DB::table('wp_postmeta')->where([
                'post_id' => $prod_id->post_id,
                'meta_key' => '_regular_price',
            ])->update(['meta_value' => intval($data[0]['price_new'] * $dollar_price),]);

            DB::table('wp_postmeta')->where([
                'post_id' => $prod_id->post_id,
                'meta_key' => '_sale_price',
            ])->update(['meta_value' =>  intval($state_data['price'] * $dollar_price)]);

                if($state_data['in_stock']){
                    DB::table('wp_postmeta')->where([
                        'post_id' => $prod_id->post_id,
                        'meta_key' => '_stock_status',
                    ])->update([
                        'meta_value' => 'instock',
                    ]);
                } else {
                    DB::table('wp_postmeta')->where([
                        'post_id' => $prod_id->post_id,
                        'meta_key' => '_stock_status',
                    ])->update(['meta_value' => 'outofstock',]);
                }
            } else {
                Log::error('ID продукта нет в базе:', [$data[0]['product_id']]);
            }
            dd($data);
    }
}
