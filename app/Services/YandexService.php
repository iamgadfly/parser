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

class YandexService
{
    public function __construct()
    {
        $this->productRepository = new ProductRepository();
    }

    public function aboutProduct($req_data)
    {
        $req_data['items'][0]['offerId'] = 10400;
        return match (count($req_data['items'])){
            1 => $this->checkProductOne($req_data),
            default => $this->checkProducts($req_data),
        };
    }

    public function checkProductOne($data)
    {
        $product = $this->productRepository->getProductById($data['items'][0]['offerId']);
        $product['_stock_status'] = 'instock';
        if($product['_stock_status'] === 'outofstock'){
            return response()->json([
                'order' => [
                    'accepted' => false,
                    'id' => (string) $data['items'][0]['id'],
                    'reason' => 'OUT_OF_STOCK',
                ],
            ]);
        } else {
            return  322;
        }
    }

    public function checkProducts($data)
    {
         return 322;
    }
}
