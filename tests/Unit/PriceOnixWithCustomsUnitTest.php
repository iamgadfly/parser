<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceOnixWithCustomsUnitTest extends TestCase
{
    public function test_get_price_onex_delivery_with_customs_onex_a()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithCustoms(80.58, 358, 21, 0.15, 1.1, 1.05);
        $this->assertEquals($check, 33509);
    }

    public function test_get_price_onex_delivery_with_customs_onex_b()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithCustoms(80.58, 235, 35, 0.15, 1.1, 1.05);
        $this->assertEquals($check, 23791);
    }

    public function test_get_price_onex_delivery_with_customs_onex_d()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithCustoms(80.58, 379, 77, 0.15, 1.1, 1.05);
        $this->assertEquals($check, 40108);
    }

    public function test_get_price_onex_delivery_with_customs_onex_c()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithCustoms(80.58, 367, 224, 0.15, 1.1, 1.05);
        $this->assertEquals($check, 51482);
    }
}
