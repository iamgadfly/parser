<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceOnixWithoutCustomsUnitTest extends TestCase
{
    public function test_get_price_onex_delivery_without_customs_1()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithoutCustoms(80.58, 425, 21, 1.1, 1.05, 0.15);
        $this->assertEquals($check, 40019);
    }

    public function test_get_price_onex_delivery_without_customs_2()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithoutCustoms(80.58, 397, 35, 1.1, 1.05, 0.15);
        $this->assertEquals($check, 38366);
    }

    public function test_get_price_onex_delivery_without_customs_3()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithoutCustoms(80.58, 381, 77, 1.1, 1.05, 0.15);
        $this->assertEquals($check, 40299);
    }

    public function test_get_price_onex_delivery_without_customs_4()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithoutCustoms(80.58, 449, 224, 1.1, 1.05, 0.15);
        $this->assertEquals($check, 59627);
    }
}
