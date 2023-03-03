<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;

class PriceDeliveryAction
{
    public function __invoke($product, $raw_price): int
    {
        $categories_ids = DB::table('wp_term_taxonomy')->where([
            ['taxonomy', '=', 'product_cat'],
            ['parent', '=', 0],
        ])->pluck('term_id')->toArray();

        $product_categories = DB::table('wp_term_relationships')
        ->where('object_id', $product->id)->pluck('term_taxonomy_id')->toArray();
        $categories = array_intersect($categories_ids, $product_categories);
        $category = DB::table('wp_terms')->where('term_id', end($categories))->first();
        $weight = match($category->slug){
            'smartfony' => 1,
            'vse-mobilnye-ustrojstva' => 1,
            'smart-chasy' => 1,
            'apple' => 1,
            'iphone' => 1,
            'planshety' => 1.5,
            'noutbuki' => 3.5,
            'monobloki' => 15,
        };
            $delivery = match(true){
            $weight == 1 && $raw_price > 450 => self::getDelivery($weight, 'Shopfans'),
            $weight == 1.5 && $raw_price > 450 =>  3222222,
            $weight == 3.5 && $raw_price > 450 => self::getDelivery($weight, 'Shopfans'),
            $weight == 15 && $raw_price > 450 => self::getDelivery($weight, 'Shopfans'),
            default => self::getDelivery($weight, 'Onex'),
        };
        $comission = match(true){
            $delivery->name == 'Onex' => 1.05,
            $delivery->name == 'Shopfans' => 1.03,
        };
        $delivery_price = (float)  match(true){
            $weight == 1 && $delivery->name == 'Shopfans'  => $delivery->price + 3,
            $weight == 1.5 && $delivery->name == 'Shopfans' => $delivery->price + 5,
            $weight == 3.5 && $delivery->name == 'Shopfans' => $delivery->price + 5,
            $weight == 15 && $delivery->name == 'Shopfans' => $delivery->price + 5,
            default => $delivery->price,
        };
        $snopfan_course = DB::table('courses')->where('name', 'Shopfans')->first();
        $dollar_course = DB::table('courses')->where('name', 'Доллар')->first();
        $price_logistic = match(true){
            $raw_price > 450 && $delivery->name = 'Shopfans' => $delivery_price * $snopfan_course->price * $comission,
            $raw_price > 380 && $raw_price < 450 && $delivery->name = 'Onex' => $delivery_price + (($raw_price - 380) * 0.15),
            default => $delivery_price * $dollar_course->price * $comission,
        };
        $price =  intval($raw_price * ($dollar_course->price * 1.1271) + $price_logistic);

        return $price;
    }

    public function getDelivery($weight, $name)
    {
        $weight_price =  DB::table('deliveries')->where([
            ['weight', '=', $weight],
            ['name', '=', $name],
        ])->first();
        return $weight_price;
    }
}
