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
            $weight == 1.5 && $raw_price > 450 =>  self::getDelivery($weight, 'Shopfans'),
            $weight == 3.5 && $raw_price > 450 => self::getDelivery($weight, 'Shopfans'),
            $weight == 15 && $raw_price > 450 => self::getDelivery($weight, 'Shopfans'),
            default => self::getDelivery($weight, 'Onex'),
        };

        $snopfan_course = DB::table('courses')->where('name', 'Shopfans')->first()->price;
        $dollar_course = DB::table('courses')->where('name', 'Доллар')->first()->price;
        $delivery = $delivery->price;

        $price = match(true){
            $weight == 1 && $raw_price > 450 => self::getPriceFirst($dollar_course, $raw_price, $delivery, $snopfan_course, 3),
            $weight > 1 && $raw_price > 450 => self::getPriceFirst($dollar_course, $raw_price, $delivery, $snopfan_course, 5),
            $weight >= 1 && $raw_price < 450 && $raw_price > 380 =>  self::getPriceSecond($dollar_course, $raw_price, $delivery),
            $weight >= 1 && $raw_price < 380 => self::getPriceThird($dollar_course, $raw_price, $delivery),
        };

        return intval($price);
    }

    public static function getPriceFirst($dollar_course, $raw_price, $delivery, $snopfan_course, $col)
    {
        return $dollar_course * ($raw_price * 1.1) + ($delivery + $col) * $snopfan_course * 1.05;
    }

    public function getPriceSecond($dollar_course, $raw_price, $delivery)
    {
        return $dollar_course * $raw_price * 1.1 + (($delivery + ($raw_price - 380) * 0.15) * $dollar_course) * 1.05;
    }

    public static function getPriceThird($dollar_course, $raw_price, $delivery)
    {
        return $dollar_course * ($raw_price * 1.1) + $delivery * $dollar_course * 1.05;
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
