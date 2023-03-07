<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        // $response = $this->get('/');

        // $response->assertStatus(200);


            // $comission = match(true){
        //     $raw_price > 450 => 1.03,
        //     default => 1.03,
        // };

        // $delivery_price = (float)  match(true){
        //     $weight == 1 && $raw_price > 450 => $delivery + 3,
        //     $weight == 1.5 && $raw_price > 450 => $delivery + 5,
        //     $weight == 3.5 && $raw_price > 450  => $delivery + 5,
        //     $weight == 15 && $raw_price > 450  => $delivery + 5,
        //     default => $delivery,
        // };


        // $price_logistic = match(true){
        //     $raw_price > 450 => $delivery_price * $snopfan_course * $comission,
        //     // $raw_price > 380 && $raw_price < 450 => $delivery_price + (($raw_price - 380) * 0.15),
        //     default => $delivery_price * $dollar_course * $comission,
        // };

        // N11 * C8 * I20 + ((J7 + (C8 - 380) * I11) * N11) * K11 )
    }
}
