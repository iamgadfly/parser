<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryRebagAction;
use PHPUnit\Framework\TestCase;

class PriceRebagUnitTest extends TestCase
{
    public function test_price_1()
    {
        $this->assertEquals(PriceDeliveryRebagAction::priceCalculate(499, 75.85, 60), "46150");
        $this->assertEquals(PriceDeliveryRebagAction::priceCalculate(600, 75, 40), "53600");
    }

    public function test_price_2()
    {
        $this->assertEquals(PriceDeliveryRebagAction::priceCalculate(400, 75, 60), "37500");
        $this->assertEquals(PriceDeliveryRebagAction::priceCalculate(1000, 75, 40), "91100");
    }
}
