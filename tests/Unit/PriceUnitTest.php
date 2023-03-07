<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryTestAction;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class PriceUnitTest extends TestCase
{
    public function test_price_iphoneA()
    {
        $check = self::getPrice('apple', 478);
        $this->assertEquals($check, 45036);
    }

    public function test_price_iphoneB()
    {
        $check = self::getPrice('apple', 425);
        $this->assertEquals($check, 40019);
    }

    public function test_price_iphoneC()
    {
        $check = self::getPrice('apple', 358);
        $this->assertEquals($check, 33509);
    }

    public function test_price_ipadA()
    {
        $check = self::getPrice('planshety', 451);
        $this->assertEquals($check, 42922);
    }

    public function test_price_ipadB()
    {
        $check = self::getPrice('planshety', 397);
        $this->assertEquals($check, 38366);
    }

    public function test_price_ipadC()
    {
        $check = self::getPrice('planshety', 235);
        $this->assertEquals($check, 23791);
    }

    public function test_price_notebookA()
    {
        $check = self::getPrice('noutbuki', 976);
        $this->assertEquals($check, 95908);
    }

    public function test_price_notebookB()
    {
        $check = self::getPrice('noutbuki', 381);
        $this->assertEquals($check, 40857);
    }

    public function test_price_notebookC()
    {
        $check = self::getPrice('noutbuki', 379);
        $this->assertEquals($check, 40109);
    }

    public function test_price_imacA()
    {
        $check = self::getPrice('monobloki', 1345);
        $this->assertEquals($check, 151433);
    }

    public function test_price_imacB()
    {
        $check = self::getPrice('monobloki', 449);
        $this->assertEquals($check, 59627);
    }

    public function test_price_imacC()
    {
        $check = self::getPrice('monobloki', 367);
        $this->assertEquals($check, 51483);
    }

    public function getPrice($category, $raw_price)
    {
        $weight = match($category){
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
            $weight == 1 && $raw_price > 450 => 30.49,
            $weight == 1 && $raw_price > 380 && $raw_price < 450   => 21,
            $weight == 1 && $raw_price < 380 => 21,

            $weight == 1.5 && $raw_price > 450 => 33.99,
            $weight == 1.5 && $raw_price < 450 && $raw_price > 380  => 35,
            $weight == 1.5 && $raw_price < 380 => 35,

            $weight == 3.5 && $raw_price > 450 => 112.99,
            $weight == 3.5 && $raw_price < 450 && $raw_price > 380  => 77,
            $weight == 3.5 && $raw_price < 380 => 77,

            $weight == 15 && $raw_price > 450 => 399.49,
            $weight == 15 && $raw_price < 450 && $raw_price > 380 => 224,
            $weight == 15 && $raw_price < 380 => 224,
        };

        $snopfan_course = 75.85;
        $dollar_course = 80.58;

        $price = match(true){
            $weight == 1 && $raw_price > 450 => $dollar_course * ($raw_price * 1.1) + ($delivery + 3) * $snopfan_course * 1.05,
            $weight == 1 && $raw_price > 380 && $raw_price < 450 => $dollar_course * $raw_price * 1.1 + (($delivery + ($raw_price - 380) * 0.15) * $dollar_course) * 1.05,
            $weight == 1 && $raw_price < 380 => $dollar_course * ($raw_price * 1.1) + $delivery * $dollar_course * 1.05,

            $weight == 1.5 && $raw_price > 450 => $dollar_course * ($raw_price * 1.1) + ($delivery + 5) * $snopfan_course * 1.05,
            $weight == 1.5 && $raw_price < 450 && $raw_price > 380  =>  $dollar_course * $raw_price * 1.1 + (($delivery + ($raw_price - 380) * 0.15) * $dollar_course) * 1.05,
            $weight == 1.5 && $raw_price < 380 => $dollar_course * ($raw_price * 1.1) + $delivery * $dollar_course * 1.05,

            $weight == 3.5 && $raw_price > 450 => $dollar_course * ($raw_price * 1.1) + ($delivery + 5) * $snopfan_course * 1.05,
            $weight == 3.5 && $raw_price < 450 && $raw_price > 380  =>  $dollar_course * $raw_price * 1.1 + (($delivery + ($raw_price - 380) * 0.15) * $dollar_course) * 1.05,
            $weight == 3.5 && $raw_price < 380 => $dollar_course * ($raw_price * 1.1) + $delivery * $dollar_course * 1.05,

            $weight == 15 && $raw_price > 450 => $dollar_course * ($raw_price * 1.1) + ($delivery + 5) * $snopfan_course * 1.05,
            $weight == 15 && $raw_price < 450 && $raw_price > 380 =>  $dollar_course * $raw_price * 1.1 + (($delivery + ($raw_price - 380) * 0.15) * $dollar_course) * 1.05,
            $weight == 15 && $raw_price < 380 => $dollar_course * ($raw_price * 1.1) + $delivery * $dollar_course * 1.05,
        };
        return intval($price);
    }
}
