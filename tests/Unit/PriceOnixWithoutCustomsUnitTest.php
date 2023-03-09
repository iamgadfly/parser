<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceOnixWithoutCustomsUnitTest extends TestCase
{
    public function test_get_price_onex_delivery_without_customs_a()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithoutCustoms(80.58, 425, 21, 1.1, 1.05, 0.15);
        $this->assertEquals($check, 40019);
    }

    public function test_get_price_onex_delivery_without_customs_b()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithoutCustoms(80.58, 397, 35, 1.1, 1.05, 0.15);
        $this->assertEquals($check, 38366);
    }

    public function test_get_price_onex_delivery_without_customs_c()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithoutCustoms(80.58, 381, 77, 1.1, 1.05, 0.15);
        // $check =  80.58 * 381 * 1.1 + ((77 + (425 - 380) * 0.15) * 80.58) * 1.05;
        $this->assertEquals($check, 40299);
    }

    public function test_get_price_onex_delivery_without_customs_d()
    {
        $check = PriceDeliveryAction::getPriceOnexDeliveryWithoutCustoms(80.58, 449, 224, 1.1, 1.05, 0.15);
        $this->assertEquals($check, 59627);
    }
}
