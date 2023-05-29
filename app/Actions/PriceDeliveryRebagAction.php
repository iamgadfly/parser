<?php

namespace App\Actions;

use Exception;

class PriceDeliveryRebagAction
{
    public static function priceCalculate($raw_price, $dollar_course, $delivery)
    {
        $price = match (true) {
            $raw_price > 1500 => new Exception('price cant be more then 1500$', 400),
            $raw_price < 0 => new Exception('price cant be less then 0$', 400),
            $raw_price <= 500 => $dollar_course * ($raw_price * 1.1) + $dollar_course * $delivery,
            $raw_price > 500 && $raw_price <= 1500 => $dollar_course * ($raw_price * 1.1) + $dollar_course * ($delivery + ($raw_price - 500) * 0.15),
        };

        return $price instanceof Exception ? $price : PriceDeliveryAction::priceRound($price, 50);
    }
}
