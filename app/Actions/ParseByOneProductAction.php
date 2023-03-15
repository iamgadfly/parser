<?php

namespace App\Actions;

use App\Services\ParserService;
use Illuminate\Support\Facades\DB;

class ParseByOneProductAction
{
    public function __construct(
        protected ParserService $parserService
    ){}

    public function __invoke($product_id, $action = new PriceDeliveryAction())
    {
        $data_product = $this->parserService->getApiBackMarket($product_id, false);
        $data_state = $this->parserService->getDataState($this->parserService->getApiBackMarket($product_id));
        $product  = DB::select(DB::raw("SELECT * FROM wp_posts p JOIN wp_postmeta pm1 ON ( pm1.post_id = p.ID) WHERE p.post_type in('product', 'product_variation') AND p.post_status = 'publish' and pm1.meta_value = '$product_id' LIMIT 1"));
        $product = $product[0] ?? null;
        $data = $this->parserService->getDataFromParsedData($product, $data_product, $data_state);

        dd($data);
//        return $this->parserService->updateParsedData($data);
    }
}
