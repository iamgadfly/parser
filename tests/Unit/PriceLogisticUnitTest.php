<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceLogisticUnitTest extends TestCase
{
    public function test_logistic_1()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(PriceDeliveryAction::getPriceLogistic(1, 478, 80.58, 30.49, 75.85, 3, 1.05), 50), 2500);
    }

    public function test_logistic_2()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(PriceDeliveryAction::getPriceLogistic(3.5, 350, 80.58, 30.49, 75.85, 3, 1.05), 50), 2450);
    }

    public function test_logistic_3()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(PriceDeliveryAction::getPriceLogistic(1.5, 600, 80.58, 30.49, 75.85, 3, 1.05), 50), 2500);
    }

    public function test_logistic_4()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(PriceDeliveryAction::getPriceLogistic(15, 799, 80.58, 30.49, 75.85, 3, 1.05), 50), 2500);
    }
}
