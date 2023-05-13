<?php

namespace App\Actions;

use App\Enums\CourseNames;
use App\Enums\Deliveries;
use Illuminate\Support\Facades\DB;

class PriceDeliveryAction
{
    public function __invoke($parent_id, int $raw_price): int
    {
        $categories_ids = DB::table('wp_term_taxonomy')->where([
            ['taxonomy', '=', 'product_cat'],
            ['parent', '=', 0],
        ])->pluck('term_id')->toArray();

        $product_categories = DB::table('wp_term_relationships')
            ->where('object_id', $parent_id)->pluck('term_taxonomy_id')->toArray();
        $categories = array_intersect($categories_ids, $product_categories);
        $category = DB::table('wp_terms')->where('term_id', end($categories))->first();

        $weight = self::getWeightByCategory($category->slug);
        $delivery = self::getDeliveryByWeightAndPrice($weight, $raw_price);

        $snopfan_course = DB::table('courses')->where('name', CourseNames::SHOPFANS)->first()->price;
        $dollar_course = DB::table('courses')->where('name', CourseNames::DOLLAR)->first()->price;
        $customs_comisson = self::getDeliveryByWeightAndPrice($weight, $raw_price);
        return intval(self::priceCalculate($weight, $raw_price, $dollar_course, $delivery, $snopfan_course, $customs_comisson, 1.1, 1.05));
    }

    public static function priceCalculate($weight, $raw_price, $dollar_course, $delivery, $snopfan_course, $customs_comisson, $agent_comission, $payment_comisson): int
    {
        $price = match (true) {
            ($weight == 1 || $weight == 1.5) && $raw_price > 450 => self::getPriceSnopfansDelivery($dollar_course, $raw_price, $delivery, $snopfan_course, $customs_comisson, $agent_comission, $payment_comisson),
            $weight > 1.5 && $raw_price >= 450 => self::getPriceSnopfansDelivery($dollar_course, $raw_price, $delivery, $snopfan_course, $customs_comisson, $agent_comission, $payment_comisson),
            $weight >= 1 && $raw_price < 450 && $raw_price > 380 => self::getPriceOnexDeliveryWithoutCustoms($dollar_course, $raw_price, $delivery, $agent_comission, $payment_comisson, $customs_comisson),
            $weight >= 1 && $raw_price <= 380 => self::getPriceOnexDeliveryWithCustoms($dollar_course, $raw_price, $delivery, $customs_comisson, $agent_comission, $payment_comisson),
            default => null,
        };
        return self::priceRound($price, 50);
    }

    public static function getPriceSnopfansDelivery($dollar_course, $raw_price, $delivery, $snopfan_course, $customs_comisson, $agent_comission, $payment_comisson)
    {
        return round($dollar_course * ($raw_price * $agent_comission) + ($delivery + $customs_comisson) * $snopfan_course * $payment_comisson);
    }

    public static function getPriceOnexDeliveryWithCustoms($dollar_course, $raw_price, $delivery, $customs_comisson, $agent_comission, $payment_comisson)
    {
        return round($dollar_course * ($raw_price * $agent_comission) + $delivery * $dollar_course * $payment_comisson);
    }

    public static function getPriceOnexDeliveryWithoutCustoms($dollar_course, $raw_price, $delivery, $agent_comission, $payment_comisson, $customs_comisson)
    {
        return round($dollar_course * $raw_price * $agent_comission + (($delivery + ($raw_price - 380) * $customs_comisson) * $dollar_course) * $payment_comisson);
    }

    public static function getPriceLogistic($weight, $raw_price, $dollar_course, $delivery, $snopfan_course, $customs_comisson, $payment_comisson)
    {
        return match (true) {
            ($weight == 1 || $weight == 1.5) && $raw_price > 450 => self::getPriceSnopfansLogistic($delivery, $snopfan_course, $customs_comisson, $payment_comisson),
            $weight > 1.5 && $raw_price >= 450 => self::getPriceSnopfansLogistic($delivery, $snopfan_course, $customs_comisson, $payment_comisson),
            $weight >= 1 && $raw_price < 450 && $raw_price > 380 => self::getPriceOnexLogisticWithoutcustoms($dollar_course, $raw_price, $delivery, $payment_comisson, $customs_comisson),
            $weight >= 1 && $raw_price <= 380 => self::getPriceOnexLogisticWithCustoms($dollar_course, $delivery, $payment_comisson, $customs_comisson),
            default => null,
        };
    }

    public static function getPriceSnopfansLogistic($delivery, $snopfan_course, $customs_comisson, $payment_comisson)
    {
        return round(($delivery + $customs_comisson) * $snopfan_course * $payment_comisson);
    }

    public static function getPriceOnexLogisticWithoutcustoms($dollar_course, $raw_price, $delivery, $payment_comisson, $customs_comisson)
    {
        return round((($delivery + ($raw_price - 380) * $customs_comisson) * $dollar_course) * $payment_comisson);
    }

    public static function getPriceOnexLogisticWithCustoms($dollar_course, $delivery, $payment_comisson, $customs_comisson)
    {
        return round(($delivery + $customs_comisson) * $dollar_course * $payment_comisson);
    }

    public static function getCustomsCommissionsByWeightAndPrice($weight, $raw_price): int | null
    {
        return match (true) {
            ($weight == 1 || $weight == 1.5) && $raw_price >= 450 => 3,
            $weight > 1.5 && $raw_price >= 450 => 5,
            $weight >= 1 && $raw_price <= 450 && $raw_price >= 380 => 0.15,
            $weight >= 1 && $raw_price <= 380 => 0.15,
            default => null,
        };
    }

    public static function getDeliveryByWeightAndPrice($weight, $raw_price)
    {
        return match (true) {
            $weight == 1 && $raw_price > 450 => self::getDelivery($weight, Deliveries::Shopfans),
            $weight == 1.5 && $raw_price > 450 => self::getDelivery($weight, Deliveries::Shopfans),
            $weight == 3.5 && $raw_price > 450 => self::getDelivery($weight, Deliveries::Shopfans),
            $weight == 15 && $raw_price > 450 => self::getDelivery($weight, Deliveries::Shopfans),
            default => self::getDelivery($weight, Deliveries::Onex),
        };
    }

    public static function priceRound(int $price, int $subcount = 50): int
    {
        return $subcount * floor($price / $subcount); //в меньшую сторону
    }

    public static function getWeightByCategory($product_category)
    {
        return match ($product_category) {
            'smartfony' => 1,
            'vse-mobilnye-ustrojstva' => 1,
            'Все мобильные устройства' => 1,
            'Смартфоны' => 1,
            'Apple' => 1,
            'iPhone' => 1,
            'Смарт-часы' => 1,
            'Apple Watch' => 1,
            'smart-chasy' => 1,
            'apple' => 1,
            'iphone' => 1,
            'Samsung' => 1,
            'planshety' => 1.5,

            'Планшеты' => 1.5,
            'iPad' => 1.5,
            'MacBook' => 3.5,
            'noutbuki' => 3.5,
            'Ноутбуки' => 3.5,
            'iMac' => 15,
            'monobloki' => 15,
            'Моноблоки' => 15,
            default => null,
        };
    }

    public static function getDelivery($weight, $name)
    {
        return DB::table('deliveries')->where([
            ['weight', '=', $weight],
            ['name', '=', $name],
        ])->first()->price ?? null;
    }
}
