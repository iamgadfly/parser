<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceCalculateUnitTest extends TestCase
{
   public function test_match_a()
   {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(1, 478, 80.58, 30.49, 75.85, 3, 1.1, 1.05), 45036);
   }

   public function test_match_b()
   {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(1.5, 451, 80.58, 33.99, 75.85, 3, 1.1, 1.05), 42921);
   }

   public function test_match_c()
   {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(3.5, 976, 80.58, 112.99, 75.85, 5, 1.1, 1.05), 95907);
   }

   public function test_match_d()
   {
        $this->assertEquals(PriceDeliveryAction::priceCalculate(15, 1345, 80.58, 399.49, 75.85, 5, 1.1, 1.05), 151432);
   }

   public function test_match_e()
   {
       $this->assertEquals(PriceDeliveryAction::priceCalculate(1.5, 397, 80.58, 35, 75.55, 0.15, 1.1, 1.05), 38366);
   }

   public function test_match_f()
   {
    $this->assertEquals(PriceDeliveryAction::priceCalculate(1.5, 235, 80.58, 35, 75.55, 0.15, 1.1, 1.05), 23791);
   }
}
