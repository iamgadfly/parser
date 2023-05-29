<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryRebagAction;
use Exception;
use PHPUnit\Framework\TestCase;

class PriceRebagUnitTest extends TestCase
{
    public function test_price_1()
    {
        $this->assertEquals(PriceDeliveryRebagAction::priceCalculate(499, 75.85, 60), "46150");
    }

    public function test_price_2()
    {
        $this->assertEquals(PriceDeliveryRebagAction::priceCalculate(1000, 75, 40), "91100");
    }
    public function test_price_3()
    {
        $this->assertEquals(PriceDeliveryRebagAction::priceCalculate(600, 75, 40), "53600");
    }

    public function test_price_4()
    {
        $this->assertEquals(PriceDeliveryRebagAction::priceCalculate(400, 75, 60), "37500");

    }

    public function test_price_5()
    {
        $check = PriceDeliveryRebagAction::priceCalculate(2000, 75, 60) instanceof Exception;
        $this->assertSame($check, true);
    }

    public function test_price_6()
    {
        $check = PriceDeliveryRebagAction::priceCalculate(-322, 75, 60) instanceof Exception;
        $this->assertSame($check, true);
    }

}
