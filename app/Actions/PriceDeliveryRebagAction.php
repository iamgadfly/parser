<?php

namespace App\Actions;

class PriceDeliveryRebagAction
{
    public static function priceCalculate($raw_price, $dollar_course, $delivery)
    {
        $price = match (true) {
            $raw_price <= 500 => $dollar_course * ($raw_price * 1.1) + $dollar_course * $delivery,
            $raw_price > 500 && $raw_price <= 1500 => $dollar_course * ($raw_price * 1.1) + $dollar_course * ($delivery + ($raw_price - 500) * 0.15),
        };
        return (string) PriceDeliveryAction::priceRound($price, 50);
    }
}
