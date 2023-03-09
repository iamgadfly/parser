<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceOnixWithCustomsUnitTest extends TestCase
{
    public function test_get_price_onex_delivery_with_customs_onex_a()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithCustoms(80.58, 350, 224, 0.15, 1.1, 1.05);
        $this->assertEquals($check, 49594);
    }

    public function test_get_price_onex_delivery_with_customs_onex_b()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithCustoms(80.58, 449, 224, 0.15, 1.1, 1.05);
        $this->assertEquals($check, 59626);
    }
}
