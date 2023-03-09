<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceSnopfansUnitTest extends TestCase
{
    public function test_get_price_snopfans_delivery_a()
    {
        $check = PriceDeliveryAction::getPriceSnopfansDelivery(80.58, 499, 30.49, 75.85, 3, 1.1, 1.05);
        $this->assertEquals($check, 46897);
    }

    public function test_get_price_snopfans_delivery_b()
    {
        $check = PriceDeliveryAction::getPriceSnopfansDelivery(80.58, 799, 30.49, 75.85, 5, 1.1, 1.05);
        $this->assertEquals($check, 73648);
    }
}
