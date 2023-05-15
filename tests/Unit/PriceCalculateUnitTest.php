<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceCalculateUnitTest extends TestCase
{
   public function test_match_1()
   {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(1, 478, 80.58, 30.49, 75.85, 3, 1.1, 1.05), PriceDeliveryAction::priceRound(45036));
   }

   public function test_match_2()
   {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(1, 425, 80.58, 21, 75.85, 0.15, 1.1, 1.05), PriceDeliveryAction::priceRound(40019));
   }

   public function test_match_3()
   {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(1, 358, 80.58, 21, 75.85, 0.15, 1.1, 1.05), PriceDeliveryAction::priceRound(33509));
   }

    public function test_match_4()
    {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(1.5, 451, 80.58, 33.99, 75.85, 3, 1.1, 1.05), PriceDeliveryAction::priceRound(42922));
    }

    public function test_match_5()
    {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(1.5, 397, 80.58, 35, 75.85, 0.15, 1.1, 1.05), PriceDeliveryAction::priceRound(38366));
    }

    public function test_match_6()
    {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(1.5, 235, 80.58, 35, 75.85, 0, 1.1, 1.05), PriceDeliveryAction::priceRound(23791));
    }

    public function test_match_7()
    {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(3.5, 976, 80.58, 112.99, 75.85, 5, 1.1, 1.05), PriceDeliveryAction::priceRound(95908));
    }

    public function test_match_8()
    {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(3.5, 381, 80.58, 77, 75.85, 0.15, 1.1, 1.05), PriceDeliveryAction::priceRound(40299));
    }

    public function test_match_9()
    {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(3.5, 379, 80.58, 77, 75.85, 0, 1.1, 1.05), PriceDeliveryAction::priceRound(40109));
    }

    public function test_match_10()
    {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(15, 1345, 80.58, 399.49, 75.85, 5, 1.1, 1.05), PriceDeliveryAction::priceRound(151433));
    }

    public function test_match_11()
    {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(15, 449, 80.58, 224, 75.85, 0.15, 1.1, 1.05), PriceDeliveryAction::priceRound(59627));
    }

    public function test_match_12()
    {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(15, 367, 80.58, 224, 75.85, 0, 1.1, 1.05), PriceDeliveryAction::priceRound(51483));
    }
}
