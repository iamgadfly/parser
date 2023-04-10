<?php

namespace App\Services;

use App\Repositories\ProductRepository;

class YandexService
{
    public $productRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
    }

    public function aboutProduct($req_data)
    {
        if (empty($req_data)) {
            return response()->json(['Error' => 'Data is invalid'], 400);
        }
        return match(count($req_data['items'])) {
            1 => $this->checkProductOne($req_data),
        default=> $this->checkProducts($req_data),
        };
    }

    public function checkProductOne($data)
    {
        $product = $this->productRepository->getProductById($data['items'][0]['offerId']);

        $accepted = match($product['_stock_status'] === 'outofstock') {
            true => false,
            false => true,
        };
        return $this->responce($accepted, $data['id']);

    }

    public function responce(bool $accepted, $order_id)
    {
        return response()->json([
            'order' => [
                'accepted' => $accepted,
                'id'       => (string) $order_id,
            ],
        ]);
    }

    public function checkProducts($data)
    {
        foreach ($data['items'] as $value) {
            $post_ids[] = $value['offerId'];
        }
        $products = $this->productRepository->getProductByIds(implode(', ', $post_ids));
        foreach ($products as $product) {
            $stocks[] = $product['_stock_status'] ?? null;
        }

        $accepted = match(!in_array('outofstock', $stocks)) {
            true => true,
            false => false,
        };

        return $this->responce($accepted, $data['id']);
    }
}
