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
            $weight == 1 && $raw_price > 450 => self::getPriceSnopfansDelivery($dollar_course, $raw_price, $delivery, $snopfan_course, 3, 1.1, 1.05),
            $weight > 1 && $raw_price > 450 => self::getPriceSnopfansDelivery($dollar_course, $raw_price, $delivery, $snopfan_course, 5, 1.1, 1.05),
            $weight >= 1 && $raw_price < 450 && $raw_price > 380 =>  self::getPriceOnexDeliveryWithCustoms($dollar_course, $raw_price, $delivery, 0.15, 1.1, 1.05),
            $weight >= 1 && $raw_price < 380 => self::getPriceOnexDeliveryWithoutCustoms($dollar_course, $raw_price, $delivery, 1.1, 1.05, 0.15),
        };

        return intval($price);
    }

    public static function priceMatch($weight, $raw_price, $dollar_course, $delivery, $snopfan_course): int
    {
        return match(true){
            $weight == 1 || $weight == 1.5 && $raw_price > 450 => self::getPriceSnopfansDelivery($dollar_course, $raw_price, $delivery, $snopfan_course, 3, 1.1, 1.05),
            $weight > 1.5 && $raw_price > 450 => self::getPriceSnopfansDelivery($dollar_course, $raw_price, $delivery, $snopfan_course, 5, 1.1, 1.05),
            $weight >= 1 && $raw_price < 450 && $raw_price > 380 =>  self::getPriceOnexDeliveryWithoutCustoms($dollar_course, $raw_price, $delivery, 1.1, 1.05, 0.15),
            $weight >= 1 && $raw_price < 380 => self::getPriceOnexDeliveryWithCustoms($dollar_course, $raw_price, $delivery, 0.15, 1.1, 1.05),
        };
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
}
