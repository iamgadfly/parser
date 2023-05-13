<?php

namespace Tests\Unit;

use App\Actions\PriceDeliveryAction;
use PHPUnit\Framework\TestCase;

class PriceLogisticUnitTest extends TestCase
{


    public function test_logistic_1()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(1, 478, 80.58, 30.49, 75.85, 3, 1.05), 2667);
    }

    public function test_logistic_2()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(1, 425, 80.58, 21, 75.85, 0.15, 1.05), 2348);
    }

    public function test_logistic_3()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(1, 358, 80.58, 21, 75.85, 0, 1.05), 1777);
    }

    public function test_logistic_4()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(1.5, 451, 80.58, 33.99, 75.85, 3, 1.05), 2946);
    }

    public function test_logistic_5()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(1.5, 397, 80.58, 35, 75.85, 0.15, 1.05), 3177);
    }

    public function test_logistic_6()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(1.5, 235, 80.58, 35, 75.85, 0, 1.05), 2961);
    }

    public function test_logistic_7()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(3.5, 976, 80.58, 112.99, 75.85, 5, 1.05), 9397);
    }

    public function test_logistic_8()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(3.5, 381, 80.58, 77, 75.85, 0.15, 1.05), 6528);
    }

    public function test_logistic_9()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(3.5, 379, 80.58, 77, 75.85, 0, 1.05), 6515);
    }

    public function test_logistic_10()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(15, 1345, 80.58, 399.49, 75.85, 5, 1.05), 32215);
    }

    public function test_logistic_11()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(15, 449, 80.58, 224, 75.85, 0.15, 1.05), 19828);
    }

    public function test_logistic_12()
    {
        $this->assertEquals(PriceDeliveryAction::getPriceLogistic(15, 367, 80.58, 224, 75.85, 0, 1.05), 18952);
    }


}
