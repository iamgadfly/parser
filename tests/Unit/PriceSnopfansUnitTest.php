<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceSnopfansUnitTest extends TestCase
{
    public function test_get_price_snopfans_delivery_1()
    {
        $check = PriceDeliveryAction::getPriceSnopfansDelivery(80.58, 478, 30.49, 75.85, 3, 1.1, 1.05);
        $this->assertEquals($check, 45036);
    }

    public function test_get_price_snopfans_delivery_2()
    {
        $check = PriceDeliveryAction::getPriceSnopfansDelivery(80.58, 451, 33.99, 75.85, 3, 1.1, 1.05);
        $this->assertEquals($check, 42922);
    }

    public function test_get_price_snopfans_delivery_3()
    {
        $check = PriceDeliveryAction::getPriceSnopfansDelivery(80.58, 976, 112.99, 75.85, 5, 1.1, 1.05);
        $this->assertEquals($check, 95908);
    }

    public function test_get_price_snopfans_delivery_4()
    {
        $check = PriceDeliveryAction::getPriceSnopfansDelivery(80.58, 1345, 399.49, 75.85, 5, 1.1, 1.05);
        $this->assertEquals($check, 151433);
    }
}
