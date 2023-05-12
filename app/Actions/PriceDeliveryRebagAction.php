<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use App\Actions\PriceDeliveryAction;

class PriceDeliveryRebagAction 
{
	public static function priceCalculate($raw_price, $dollar_course)
	{
		$price = match(true){
			$raw_price <= 500 => $dollar_course * $raw_price + 60 * 1.1,  
			$raw_price > 500 && $raw_price <= 1500 => ($dollar_course * $raw_price + $dollar_course * (40 + ($raw_price - 500)) * 0.15) * 1.1,
		};
		
		return (string) PriceDeliveryAction::priceRound($price, 50);
	}
}
