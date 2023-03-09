<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceOnixWithoutCustomsUnitTest extends TestCase
{
    public function test_get_price_onex_delivery_without_customs_a()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithoutCustoms(80.58, 449, 224, 1.1, 1.05);
        $this->assertEquals($check, 58750);
    }

    public function test_get_price_onex_delivery_without_customs_b()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithoutCustoms(80.58, 349, 224, 1.1, 1.05);
        $this->assertEquals($check, 49887);
    }
}
