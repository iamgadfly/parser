<?php

namespace App\Http\Controllers;

use App\Imports\ProductsImport;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

// use GuzzleHttp\Psr7\Request;

class ParserController extends Controller
{
    public function index(Request $request){
        $file = $request->file('file')->get();
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
                if ($value['backboxGrade'] || $value['isDisabled'] || $value['price']) {
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
                'model' => $response_data['model'],
                'link' => $response_data['links']['US']['href'] ?? null,
                'model_about' => $response['subTitleElements'],
                'states' => $data_state ?? null,
            ];
            unset($data_state);
        }
         dd($data);
    }
}
