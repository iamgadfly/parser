<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceRoundUnitTest extends TestCase
{
    public function test_round_1()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(120, 50), 100);
    }

    public function test_round_2()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(220, 50), 200);
    }

    public function test_round_3()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(330, 50), 300);
    }

    public function test_round_4()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(16479, 50),  16450);
    }

    public function test_round_5()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(27429, 50), 27400);
    }

    public function test_round_6()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(45330, 50), 45300);
    }
    public function test_round_7()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(54972, 50), 54950);
    }
    public function test_round_8()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(35124, 50), 35100);
    }
    public function test_round_9()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(12881, 50), 12850);
    }
    public function test_round_10()
    {
        $this->assertEquals(PriceDeliveryAction::priceRound(5123, 50), 5100);
    }
}
